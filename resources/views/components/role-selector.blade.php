<div class="row">
    @foreach ($roles as $role)
        <div class="col-sm-12  mt-5">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input name="roles[]" class="form-check-input" type="checkbox" value="{{ $role->uid }}"
                    id="role_{{ $role->id }}" @if (@$company && $company->hasRole($role->name)) checked="checked" @endif />
                <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->name }}
                </label>
            </div>
        </div>
    @endforeach
</div>
