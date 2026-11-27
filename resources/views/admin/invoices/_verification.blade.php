<td class="verification">
    @if($verificationUrl)
        @php
            $renderer = new \BaconQrCode\Renderer\GDLibRenderer(320);
            $qrCode = base64_encode((new \BaconQrCode\Writer($renderer))->writeString($verificationUrl));
        @endphp
        <a href="{{ $verificationUrl }}">
            <img src="data:image/png;base64,{{ $qrCode }}" alt="">
            <br>{{ $labels['verify_invoice'] }}
        </a>
    @endif
</td>
