@php
    $letterheadName = $company?->legal_name ?: 'PT SITYOY IT SOLUTIONS';
    $letterheadAddress = $company?->company_address
        ?: 'Jl. Cendana II, Blok C1 No. 28, Sukamantri, Pasar Kemis, Kabupaten Tangerang, Banten 15560';
    $letterheadEmail = $company?->company_email ?: 'sityoyitsolutions@gmail.com';
    $letterheadPhone = $company?->company_phone ?: '0813-8320-5359';
@endphp

<table class="letterhead">
    <tr>
        <td class="letterhead-logo" width="27%">
            @if(file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Logo SIS.COM">
            @endif
        </td>
        <td class="letterhead-info" width="73%">
            <div class="letterhead-name">
                {{ strtoupper($letterheadName) }}
            </div>
            <div class="letterhead-tagline">IT SOLUTIONS FOR A BETTER FUTURE</div>
            <div class="letterhead-services">
                Software Development &bull; IT Consulting &bull; IT Support &bull; Computer Services
            </div>
            <div>{{ $letterheadAddress }}</div>
            <div class="letterhead-contact">
                {{ $letterheadEmail }}
                <span class="letterhead-separator">|</span> {{ $letterheadPhone }}
            </div>
        </td>
    </tr>
</table>
