@php
    $currentPerPage = (string) request('per_page', '10');
@endphp

<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mt-3 gap-2 w-100">
    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
        <small class="text-muted">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}
            dari {{ $paginator->total() }} data
        </small>

        <form method="GET" action="{{ url()->current() }}" class="d-inline-flex align-items-center"
              data-no-submit-lock>
            @foreach(request()->except(['page', 'per_page']) as $key => $value)
                @if(is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <label for="per-page-{{ $paginator->getPageName() }}" class="sr-only">Data per halaman</label>
            <select id="per-page-{{ $paginator->getPageName() }}" name="per_page"
                    class="custom-select custom-select-sm" style="width: auto; min-height: 34px;"
                    onchange="this.form.submit()">
                @foreach([10, 15, 20, 50] as $option)
                    <option value="{{ $option }}" @selected($currentPerPage === (string) $option)>
                        {{ $option }} data
                    </option>
                @endforeach
                <option value="all" @selected($currentPerPage === 'all')>Semua</option>
            </select>
        </form>
    </div>

    @if($paginator->hasPages())
        <div>
            {{ $paginator->withQueryString()->onEachSide(1)->links() }}
        </div>
    @endif
</div>
