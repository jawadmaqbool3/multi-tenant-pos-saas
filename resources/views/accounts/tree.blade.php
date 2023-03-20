<ul class="list-unstyled">
    @foreach ($accounts as $account)
        <li>
            <span class="mt-3 d-block">
                <span
                    class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($account->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                            fill="currentColor"></path>
                        <path opacity="0.3"
                            d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                            fill="currentColor"></path>
                    </svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3"
                                    d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                    fill="currentColor"></path>
                                <path
                                    d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                    fill="currentColor"></path>
                            </svg>
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
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                    fill="currentColor"></path>
                                <path opacity="0.5"
                                    d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                    fill="currentColor"></path>
                                <path opacity="0.5"
                                    d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                    </a>
                @endif
            </span>
            <ul class="list-unstyled ms-10 mt-2 border-top border-secondary border-2">
                @foreach ($account->level2 as $level2)
                    <li>
                        <span class="mt-3 d-block"><span
                                class="svg-icon svg-icon-2 btn btn-sm btn-icon @if ($level2->status == 'active') btn-color-primary @else btn-color-danger @endif btn-active-light-primary">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                                        fill="currentColor"></path>
                                    <path opacity="0.3"
                                        d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                                        fill="currentColor"></path>
                                </svg>
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path opacity="0.3"
                                                d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                fill="currentColor"></path>
                                            <path
                                                d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                fill="currentColor"></path>
                                        </svg>
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
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                fill="currentColor"></path>
                                            <path opacity="0.5"
                                                d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                fill="currentColor"></path>
                                            <path opacity="0.5"
                                                d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                fill="currentColor"></path>
                                        </svg>
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
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                                                    fill="currentColor"></path>
                                                <path opacity="0.3"
                                                    d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                                                    fill="currentColor"></path>
                                            </svg>
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
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path opacity="0.3"
                                                            d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                            fill="currentColor"></path>
                                                        <path
                                                            d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                            fill="currentColor"></path>
                                                    </svg>
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
                                                    <svg width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                            fill="currentColor"></path>
                                                        <path opacity="0.5"
                                                            d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                            fill="currentColor"></path>
                                                        <path opacity="0.5"
                                                            d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                            fill="currentColor"></path>
                                                    </svg>
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
                                                        <svg width="24" height="24" viewBox="0 0 24 24"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                                                                fill="currentColor"></path>
                                                            <path opacity="0.3"
                                                                d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                                                                fill="currentColor"></path>
                                                        </svg>
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
                                                                <svg width="24" height="24"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path opacity="0.3"
                                                                        d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                                        fill="currentColor"></path>
                                                                    <path
                                                                        d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
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
                                                                <svg width="24" height="24"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                                        fill="currentColor"></path>
                                                                    <path opacity="0.5"
                                                                        d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                                        fill="currentColor"></path>
                                                                    <path opacity="0.5"
                                                                        d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                                        fill="currentColor"></path>
                                                                </svg>
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
