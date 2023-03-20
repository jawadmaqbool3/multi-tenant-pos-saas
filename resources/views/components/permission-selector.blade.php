<div class="row">
    @php
        $previous = null;
    @endphp
    @foreach ($permissions as $permission)
        @if ($permission->module_id != $previous)
            @php
                $previous = $permission->module_id;
            @endphp
            <div class="col-md-12">
                <h5 class="mt-10 border-bottom mb-5">{{ $permission->module->name }}
                    <div class=" float-end form-check form-switch form-check-custom form-check-solid">
                        <input data-target=".module{{ $permission->module_id }}" class="form-check-input check-all"
                            type="checkbox">
                    </div>
                </h5>
            </div>
        @endif
        <div class="col-sm-6 col-md-4  mt-2 ">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input name="permissions[]" class="form-check-input permission-check module{{ $permission->module_id }}"
                    type="checkbox" id="permission_{{ $permission->id }}" data-target="module{{ $permission->module_id }}" value="{{ $permission->uid }}"
                    @if ((@$role && $role->hasPermission($permission->name)) || (@$company && $company->hasPermission($permission->name))) checked="checked" @endif />
                <label class="form-check-label" for="permission_{{ $permission->id }}">
                    {{ $permission->name }}
                </label>
            </div>
        </div>
    @endforeach
</div>

<script>
    
</script>