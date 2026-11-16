@foreach(['success' => 'check-circle', 'warning' => 'exclamation-triangle', 'error' => 'times-circle', 'info' => 'info-circle'] as $type => $icon)
    @if(session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} sis-alert alert-dismissible fade show" role="alert">
            <i class="fas fa-{{ $icon }}"></i>
            <span>{{ session($type) }}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endforeach

@if($errors->any())
    <div class="alert alert-danger sis-alert alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Data belum dapat disimpan.</strong>
            <ul class="mb-0 mt-1 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
