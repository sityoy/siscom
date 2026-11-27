[1mdiff --git a/resources/views/admin/agreements/pdf.blade.php b/resources/views/admin/agreements/pdf.blade.php[m
[1mindex 7485eef..78f0fa0 100644[m
[1m--- a/resources/views/admin/agreements/pdf.blade.php[m
[1m+++ b/resources/views/admin/agreements/pdf.blade.php[m
[36m@@ -65,9 +65,12 @@[m
     @endforeach[m
 [m
[31m-    @include('pdf.signatures', [[m
[31m-    'placeDate' => $agreement->city.', '.$agreement->document_date->locale('id')->translatedFormat('d F Y'),[m
[31m-    'issuer' => ['Pihak Pertama,', $agreement->provider_title, $agreement->provider_signer, $agreement->provider_name],[m
[31m-    'recipient' => ['Pihak Kedua,', $agreement->client_title, $agreement->client_signer, $agreement->client_name],[m
[31m-])[m
[32m+[m[32m    <table class="signatures">[m
[32m+[m[32m        <tr><td><strong>Pihak Pertama</strong></td><td><strong>Pihak Kedua</strong></td></tr>[m
[32m+[m[32m        <tr><td>{{ $agreement->provider_title }}</td><td>{{ $agreement->client_title }}</td></tr>[m
[32m+[m[32m        <tr><td>{{ $agreement->provider_name }}</td><td>{{ $agreement->client_name }}</td></tr>[m
[32m+[m[32m        <tr><td class="signature-space"></td><td></td></tr>[m
[32m+[m[32m        <tr><td><strong>{{ $agreement->provider_signer }}</strong></td><td><strong>{{ $agreement->client_signer }}</strong></td></tr>[m
[32m+[m[41m        [m
[32m+[m[32m    </table>[m
 </body>[m
 </html>[m
[1mdiff --git a/resources/views/admin/invoices/pdf.blade.php b/resources/views/admin/invoices/pdf.blade.php[m
[1mindex eeaad5d..b90079b 100644[m
[1m--- a/resources/views/admin/invoices/pdf.blade.php[m
[1m+++ b/resources/views/admin/invoices/pdf.blade.php[m
[36m@@ -359,9 +359,24 @@[m
 </table>[m
 [m
[31m-@include('pdf.signatures', [[m
[31m-    'placeDate' => $city.', '.$formatDate($invoice->invoice_date ?? $invoice->created_at),[m
[31m-    'issuer' => [$labels['issued_by'], $directorTitle, $directorName, $companyName],[m
[31m-    'recipient' => [$labels['approved_by'], $schoolSignerTitle, $schoolSignerName, $invoice->client->name],[m
[31m-])[m
[32m+[m[32m<table class="signatures">[m
[32m+[m[32m    <tr>[m
[32m+[m[32m        <td>[m
[32m+[m[32m            {{ $city }}, {{ $formatDate($invoice->invoice_date ?? $invoice->created_at) }}<br>[m
[32m+[m[32m            {{ $labels['issued_by'] }}<br><strong>{{ $directorTitle }}</strong>[m
[32m+[m[32m            <br> {{ $companyName }}[m
[32m+[m[32m            <div class="signature-space"></div>[m
[32m+[m[32m            <span class="signer-name">{{ $directorName ?: '(...................................)' }}</span>[m
[32m+[m[41m            [m
[32m+[m[32m        </td>[m
[32m+[m[32m        <td>[m
[32m+[m[32m            &nbsp;<br>[m
[32m+[m[32m            {{ $labels['approved_by'] }}<br><strong>{{ $schoolSignerTitle }}</strong>[m
[32m+[m[32m            <br> {{ $invoice->client->name }}[m
[32m+[m[32m            <div class="signature-space"></div>[m
[32m+[m[32m            <span class="signer-name">{{ $schoolSignerName ?: '(...................................)' }}</span>[m
[32m+[m[41m            [m
[32m+[m[32m        </td>[m
[32m+[m[32m    </tr>[m
[32m+[m[32m</table>[m
 [m
 <div class="footer">[m
