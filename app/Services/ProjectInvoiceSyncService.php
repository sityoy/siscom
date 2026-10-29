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
                $vat = $subtotal * ((float) $invoice->vat_percent / 100);

                $item->update([
                    'description' => 'Biaya Project - '.$project->title,
                    'price' => $price,
                    'total' => $subtotal,
                ]);

                $invoice->update([
                    'subtotal' => $subtotal,
                    'vat' => $vat,
                    'grand_total' => $subtotal + $vat + (float) $invoice->service_fee,
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
        ]);
    }
}
