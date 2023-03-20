@switch($accountLevel)
    @case('all')
        <select name="{{ $name }}" {{ $isRequired ?: 'required' }}
            class="form-control form-control-solid {{ $class }} _select2" id="{{ $id }}">
            <option value="" @if ($selected == 'all') selected @endif data-level="all">None</option>
            @foreach ($accounts as $level1)
                <option data-level="level1" @if ($selected == $level1->uid) selected @endif value="{{ $level1->uid }}">
                    <strong>{{ $level1->name }}</strong>
                </option>
                @if ($selectLevel != 'level1')
                    @foreach ($level1->level2Active as $level2)
                        <option data-level="level2" @if ($selected == $level2->uid) selected @endif value="{{ $level2->uid }}">
                            &nbsp;&nbsp;<strong>{{ $level2->name }}</strong></option>
                        @if ($selectLevel != 'level2')
                            @foreach ($level2->level3Active as $level3)
                                <option data-level="level3" @if ($selected == $level3->uid) selected @endif
                                    value="{{ $level3->uid }}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $level3->name }}</strong></option>
                                @if ($selectLevel != 'level3')
                                    @foreach ($level3->level4Active as $level4)
                                        <option data-level="level4" @if ($selected == $level4->uid) selected @endif
                                            value="{{ $level4->uid }}">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $level4->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        </select>
    @break

    @case('level1')
        <select name="{{ $name }}" {{ $isRequired ?: 'required' }}
            class="form-control form-control-solid {{ $class }} _select2" id="{{ $id }}">
            <option value="" @if ($selected == 'all') selected @endif data-level="all">None</option>
            <option value="{{ @$accounts->uid }}">{{ $accounts->name }}</option>
            @if ($selectLevel != 'level1')
                @foreach ($accounts->level2Active as $level2)
                    <option data-level="level2" @if ($selected == $level2->uid) selected @endif value="{{ $level2->uid }}">
                        &nbsp;&nbsp;<strong>{{ $level2->name }}</strong></option>
                    @if ($selectLevel != 'level2')
                        @foreach ($level2->level3Active as $level3)
                            <option data-level="level3" @if ($selected == $level3->uid) selected @endif
                                value="{{ $level3->uid }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;<strong>{{ $level3->name }}</strong></option>
                            @if ($selectLevel != 'level3')
                                @foreach ($level3->level4Active as $level4)
                                    <option data-level="level4" @if ($selected == $level4->uid) selected @endif
                                        value="{{ $level4->uid }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $level4->name }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif
        </select>
    @break

    @case('level2')
        <select name="{{ $name }}" {{ $isRequired ?: 'required' }}
            class="form-control form-control-solid {{ $class }} _select2" id="{{ $id }}">
            <option value="" @if ($selected == 'all') selected @endif data-level="all">None</option>
            <option value="{{ @$accounts->uid }}"><strong>{{ $accounts->name }}</strong></option>
            @if ($selectLevel != 'level2')
                @foreach ($accounts->level3Active as $level3)
                    <option data-level="level3" @if ($selected == $level3->uid) selected @endif
                        value="{{ $level3->uid }}">
                        &nbsp;&nbsp;<strong>{{ $level3->name }}</strong></option>
                    @if ($selectLevel != 'level3')
                        @foreach ($level3->level4Active as $level4)
                            <option data-level="level4" @if ($selected == $level4->uid) selected @endif
                                value="{{ $level4->uid }}">
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $level4->name }}</option>
                        @endforeach
                    @endif
                @endforeach
            @endif
        </select>
    @break

    @case('level3')
        <select name="{{ $name }}" {{ $isRequired ?: 'required' }}
            class="form-control form-control-solid {{ $class }} _select2" id="{{ $id }}">
            <option value="" @if ($selected == 'all') selected @endif data-level="all">None</option>
            <option value="{{ @$accounts->uid }}">{{ $accounts->name }}</option>
            @if ($selectLevel != 'level3')
                @foreach ($accounts->level4Active as $level4)
                    <option data-level="level4" @if ($selected == $level4->uid) selected @endif
                        value="{{ $level4->uid }}">
                        &nbsp;&nbsp;{{ $level4->name }}</option>
                @endforeach
            @endif
        </select>
    @break

    @case('level4')
        <input type="hidden" value="{{ @$accounts->uid }}" name="{{ $name }}" id="{{ $id }}">
    @break

@endswitch

</select>
