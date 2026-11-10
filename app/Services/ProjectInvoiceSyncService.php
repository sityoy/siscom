<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ProcurementDocument;
use App\Models\Project;

class ProjectInvoiceSyncService
{
    public function primaryInvoice(Project $project): ?Invoice
    {
        return $project->invoices()
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->first();
    }

    public function syncOpenInvoicesFromProject(Project $project): void
    {
        $project->invoices()
            ->with('items')
            ->where('invoice_type', 'project')
            ->whereIn('status', ['unpaid', 'partial'])
            ->each(function (Invoice $invoice) use ($project) {
                $invoice->update([
                    'client_id' => $project->client_id,
                    'late_fee_active' => $project->late_fee_active,
                    'late_fee_per_month' => $project->late_fee_active
                        ? $project->late_fee_per_month
                        : 0,
                ]);

                if ($invoice->items->count() !== 1) {
                    return;
                }

                $item = $invoice->items->first();

                if (!str_starts_with($item->description, 'Biaya Project - ')) {
                    return;
                }

                $qty = max(1, (float) $item->qty);
                $price = (float) ($project->budget ?? 0);
                $subtotal = $qty * $price;
                $vatPercent = $invoice->provider_tax_status === 'pkp'
                    ? (float) $invoice->vat_percent
                    : 0;
                $vat = $subtotal * ($vatPercent / 100);

                $item->update([
                    'description' => 'Biaya Project - '.$project->title,
                    'price' => $price,
                    'total' => $subtotal,
                ]);

                $invoice->update([
                    'subtotal' => $subtotal,
                    'vat_percent' => $vatPercent,
                    'vat' => $vat,
                    'grand_total' => $subtotal + $vat + (float) $invoice->service_fee,
                    'income_tax_base' => $subtotal + (float) $invoice->service_fee,
                    'income_tax_amount' => $invoice->income_tax_withheld
                        ? ($subtotal + (float) $invoice->service_fee)
                            * (float) $invoice->income_tax_rate / 100
                        : 0,
                ]);
            });

        $this->syncProjectPackage($project);
    }

    public function syncAfterInvoiceSaved(
        Invoice $invoice,
        ?int $previousProjectId = null,
    ): void {
        if ($previousProjectId && $previousProjectId !== $invoice->project_id) {
            ProcurementDocument::where('project_id', $previousProjectId)
                ->where('invoice_id', $invoice->id)
                ->update(['invoice_id' => null]);

            $previousProject = Project::find($previousProjectId);

            if ($previousProject) {
                $this->syncProjectPackage($previousProject);
            }
        }

        if ($invoice->project) {
            $this->syncProjectPackage($invoice->project);
        }
    }

    public function syncProjectPackage(Project $project): void
    {
        $invoice = $this->primaryInvoice($project);

        if (!$invoice) {
            $project->procurementDocuments()->update(['invoice_id' => null]);

            return;
        }

        $invoiceDate = $invoice->invoice_date
            ?? $invoice->created_at
            ?? now();

        $project->procurementDocuments()->update([
            'invoice_id' => $invoice->id,
            'document_date' => $invoiceDate->toDateString(),
            'subject' => $project->title,
            'amount' => $invoice->total_due,
            'provider_tax_status' => $invoice->income_tax_scheme === 'umkm_final'
                ? 'umkm_final'
                : $invoice->provider_tax_status,
            'tax_base' => $invoice->income_tax_base,
            'vat_amount' => $invoice->vat,
            'income_tax_amount' => $invoice->income_tax_amount,
        ]);

        $package = $project->procurementPackage;

        if ($package) {
            $contractValue = (float) $invoice->grand_total;
            $vendorCost = $package->package_type === 'third_party'
                ? (float) $package->vendor_cost
                : 0;
            $incomeTaxRate = (float) $package->income_tax_rate;
            $incomeTaxAmount = (float) ($invoice->income_tax_base ?: $contractValue)
                * $incomeTaxRate / 100;
            $managementFee = $contractValue - $vendorCost;

            $package->update([
                'contract_value' => $contractValue,
                'management_fee_amount' => $managementFee,
                'income_tax_amount' => $incomeTaxAmount,
                'net_profit' => $managementFee
                    - (float) $package->operational_cost
                    - (float) $package->partner_fee
                    - $incomeTaxAmount,
            ]);
        }
    }
}
