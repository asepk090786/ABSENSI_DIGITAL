@php
    $params = $params ?? [];
    $defaults = $defaults ?? [];
    $perPage = $params['per_page'] ?? $defaults['per_page'] ?? 10;
    $sort = $params['sort'] ?? $defaults['sort'] ?? 'desc';
@endphp

<div class="d-flex align-items-center gap-2">
    <form method="get" class="d-flex gap-2">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <div class="input-group input-group-sm">
            <label class="input-group-text">Tampilkan</label>
            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>
