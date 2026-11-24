<table style="margin-top:18pt;width:100%;table-layout:fixed;border-collapse:collapse;page-break-inside:avoid;color:#19263b;text-align:center;font-size:9pt">
    <tr>
        <td style="width:40%;text-align:center;padding:0 4pt">{{ $placeDate }}</td>
        <td style="width:20%"></td><td style="width:40%"></td>
    </tr>
    <tr>
        @foreach([$issuer, $recipient] as $party)
            @if($loop->last)<td></td>@endif
            <td style="vertical-align:top;text-align:center;padding:3pt 4pt">
                {{ $party[0] }}<br><strong>{{ $party[1] }}</strong>
                <div style="height:48pt"></div>
                <strong style="text-decoration:underline">{{ $party[2] ?: '(...................................)' }}</strong><br>
                {{ $party[3] }}
            </td>
        @endforeach
    </tr>
</table>
