<h2>Invoice Baru</h2>

<p>
    Halo {{ $invoice->client->name }},
</p>

<p>
    Invoice baru telah dibuat.
</p>

<p>

    Nomor Invoice:
    <b>{{ $invoice->invoice_number }}</b>

</p>

<p>

    Total:
    <b>

        Rp
        {{ number_format($invoice->amount,0,',','.') }}

    </b>

</p>

<p>
    Terima kasih.
</p>
