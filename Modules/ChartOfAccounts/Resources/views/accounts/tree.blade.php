<ul class="list-unstyled">
    @foreach ($accounts as $account)
        <li>
            <span class="mt-3 d-block">
                <span
                    class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($account->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary">
                    <x-icon name="check-circle" :size="16" />
                </span>
                <b>
                    {{ $account->start_code }} - {{ $account->name }} </b>
                <b class="float-end mt-2">({{ $account->calculatebalance() }})</b>

                @if (auth()->user()->hasPermission('edit account'))
                    <a data-bs-toggle="modal" data-bs-target="#edit_modal" data-modal_id="{{ $account->uid }}"
                        data-modal_account="" title="edit account" data-modal_name="{{ $account->name }}"
                        data-modal_status="{{ $account->status }}" data-modal_level="level1"
                        class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">
                        <span class="svg-icon svg-icon-3">
                            <x-icon name="pencil" :size="16" />
                        </span>
                    </a>
                @endif
                @if (auth()->user()->hasPermission('delete account'))
                    <a data-bs-toggle="modal" data-bs-target="#delete_modal" href="#" title="delete account"
                        data-bs-target="#delete_modal" data-bs-toggle="modal" data-modal_id="{{ $account->uid }}"
                        data-modal_name="{{ $account->name }}" data-modal_title="Delete {{ $account->name }}?"
                        data-modal_level="level1"
                        class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3">
                            <x-icon name="trash" :size="16" />
                        </span>
                    </a>
                @endif
            </span>
            <ul class="list-unstyled ms-10 mt-2 border-top border-secondary border-2">
                @foreach ($account->level2 as $level2)
                    <li>
                        <span class="mt-3 d-block"><span
                                class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($level2->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary">
                                <x-icon name="check-circle" :size="16" />
                            </span>

                            {{ $level2->code }} - {{ $level2->name }}
                            <b class="float-end mt-2">({{ $level2->calculatebalance() }})</b>
                            @if (auth()->user()->hasPermission('edit account'))
                                <a data-modal_id="{{ $level2->uid }}" data-bs-toggle="modal"
                                    data-bs-target="#edit_modal" data-modal_status="{{ $level2->status }}"
                                    data-modal_account="{{ $level2->level1->uid }}"
                                    data-modal_name="{{ $level2->name }}" title="edit account"
                                    data-modal_level="level2"
                                    class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">
                                    <span class="svg-icon svg-icon-3">
                                        <x-icon name="pencil" :size="16" />
                                    </span>
                                </a>
                            @endif
                            @if (auth()->user()->hasPermission('delete account'))
                                <a data-bs-toggle="modal" data-bs-target="#delete_modal" href="#"
                                    title="delete account" data-bs-target="#delete_modal" data-bs-toggle="modal"
                                    data-modal_id="{{ $level2->uid }}" data-modal_name="{{ $level2->name }}"
                                    data-modal_title="Delete {{ $level2->name }}?" data-modal_level="level2"
                                    class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                                    <span class="svg-icon svg-icon-3">
                                        <x-icon name="trash" :size="16" />
                                    </span>
                                </a>
                            @endif
                        </span>
                        <ul class="list-unstyled ms-10 mt-2 border-top border-secondary border-2">
                            @foreach ($level2->level3 as $level3)
                                <li>
                                    <span class="mt-3 d-block">
                                        <span
                                            class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($level3->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary">
                                            <x-icon name="check-circle" :size="16" />
                                        </span>
                                        {{ $level3->code }} -{{ $level3->name }}
                                        <b class="float-end mt-2">({{ $level3->calculatebalance() }})</b>
                                        @if (auth()->user()->hasPermission('edit account'))
                                            <a data-bs-toggle="modal" data-modal_name="{{ $level3->name }}"
                                                data-modal_id="{{ $level3->uid }}" data-bs-target="#edit_modal"
                                                data-modal_status="{{ $level3->status }}" data-modal_level="level3"
                                                data-modal_account="{{ $level3->level2->uid }}" title="edit account"
                                                class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">
                                                <span class="svg-icon svg-icon-3">
                                                    <x-icon name="pencil" :size="16" />
                                                </span>
                                            </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete account'))
                                            <a data-bs-toggle="modal" data-bs-target="#delete_modal" href="#"
                                                title="delete account" data-bs-target="#delete_modal"
                                                data-bs-toggle="modal" data-modal_level="level3"
                                                data-modal_id="{{ $level3->uid }}"
                                                data-modal_name="{{ $level3->name }}"
                                                data-modal_title="Delete {{ $level3->name }}?"
                                                class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                                                <span class="svg-icon svg-icon-3">
                                                    <x-icon name="trash" :size="16" />
                                                </span>
                                            </a>
                                        @endif
                                    </span>
                                    <ul class="list-unstyled ms-10 mt-2 border-top border-secondary border-2">
                                        @foreach ($level3->level4 as $level4)
                                            <li class="border-secondary border-bottom">
                                                <span class="my-3 d-block">
                                                    <i
                                                        class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($level4->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary ">
                                                        <x-icon name="check-circle" :size="16" />
                                                    </i>
                                                    <a href="http://localhost/ssss/general/report/5/filter"
                                                        style="text-decoration: none"> {{ $level4->code }} -
                                                        {{ $level4->name }} </a>
                                                    <b class="float-end mt-2">({{ $level4->calculatebalance() }})</b>
                                                    @if (auth()->user()->hasPermission('edit account'))
                                                        <a data-bs-toggle="modal" data-bs-target="#edit_modal"
                                                            data-modal_status="{{ $level4->status }}"
                                                            data-modal_level="level4"
                                                            data-modal_account="{{ $level4->level3->uid }}"
                                                            data-modal_name="{{ $level4->name }}"
                                                            data-modal_id="{{ $level4->uid }}" title="edit account"
                                                            class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">
                                                            <span class="svg-icon svg-icon-3">
                                                                <x-icon name="pencil" :size="16" />
                                                            </span>
                                                        </a>
                                                    @endif
                                                    @if (auth()->user()->hasPermission('delete account'))
                                                        <a data-bs-toggle="modal" data-bs-target="#delete_modal"
                                                            data-modal_level="level4"
                                                            data-modal_id="{{ $level4->uid }}" href="#"
                                                            title="delete account" data-bs-target="#delete_modal"
                                                            data-bs-toggle="modal"
                                                            data-modal_name="{{ $level4->name }}"
                                                            data-modal_title="Delete {{ $level4->name }}?"
                                                            class="me-3 mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                                                            <span class="svg-icon svg-icon-3">
                                                                <x-icon name="trash" :size="16" />
                                                            </span>
                                                        </a>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
</ul>
