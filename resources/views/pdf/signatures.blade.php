<table style="margin-top:18pt;width:100%;table-layout:fixed;border-collapse:collapse;page-break-inside:avoid;color:#19263b;text-align:center;font-size:9pt;line-height:1.5">
    <tr>
        <td style="width:40%;padding:0 5pt 2pt">{{ $placeDate }}</td>
        <td style="width:20%"></td>
        <td style="width:40%"></td>
    </tr>
    <tr>
        @foreach([$issuer, $recipient] as $party)
            @if($loop->last)<td></td>@endif
            <td style="vertical-align:top;padding:1pt 5pt 0;overflow-wrap:break-word">
                {{ $party[0] }}<br>
                <strong>{{ $party[1] }}</strong><br>
                {{ $party[3] }}
            </td>
        @endforeach
    </tr>
    <tr>
        <td colspan="3" style="height:48pt"></td>
    </tr>
    <tr>
        @foreach([$issuer, $recipient] as $party)
            @if($loop->last)<td></td>@endif
            <td style="vertical-align:top;padding:0 5pt">
                <strong style="text-decoration:underline">{{ $party[2] ?: '(...................................)' }}</strong>
            </td>
        @endforeach
    </tr>
</table>
