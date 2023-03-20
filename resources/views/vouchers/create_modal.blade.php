<div class="modal fade" id="create_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form data-ajax="true" class="form" id="kt_modal_new_address_form" method="post"
                data-base-url="{{ route('voucher-type.store') }}">
                <div class="modal-header" id="kt_modal_new_address_header">
                    <h2 id="modal_title">Create Voucher Type</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2"
                                    rx="1" transform="rotate(-45 6 17.3137)" fill="currentColor" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                    transform="rotate(45 7.41422 6)" fill="currentColor" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="" class="form-label">Name</label>
                                <input type="text" name="name" id="modal_name" class="form-control"
                                    placeholder="Type Name" aria-describedby="helpId" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="" class="form-label">Short</label>
                                <input type="text" name="name" id="modal_short" class="form-control"
                                    placeholder="Type Name" aria-describedby="helpId" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="" class="form-label">Credit Account</label>
                                <select class="form-control select2 account_selection select2-hidden-accessible"
                                    id="account_level_3" required="" name="account_level_3"
                                    data-select2-id="account_level_3" tabindex="-1" aria-hidden="true">
                                    <option value="" selected="" disabled="" data-select2-id="6">Select
                                        Option</option>
                                    <option value="" disabled="" style="font-weight:bolder"
                                        data-select2-id="12">Asset</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="13">&nbsp;&nbsp;&nbsp;&nbsp; Current Assets</option>
                                    <option value="175" data-select2-id="14">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Banks</option>
                                    <option value="176" data-select2-id="15">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Cash in Hand</option>
                                    <option value="234" data-select2-id="16">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Advances/Loan Receivable
                                    </option>
                                    <option value="243" data-select2-id="17">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; OTHER RECEIVABLE</option>
                                    <option value="244" data-select2-id="18">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ACCRUED INCOME</option>
                                    <option value="262" data-select2-id="19">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Stocks</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="20">&nbsp;&nbsp;&nbsp;&nbsp; Non-Current Assets</option>
                                    <option value="233" data-select2-id="21">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SECURITY &amp; DEPOSITS
                                    </option>
                                    <option value="235" data-select2-id="22">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; LAND</option>
                                    <option value="236" data-select2-id="23">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; OFFICE EQUIPMENT</option>
                                    <option value="237" data-select2-id="24">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; FURNITURE &amp; FIXTURE
                                    </option>
                                    <option value="238" data-select2-id="25">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; VEHICLES</option>
                                    <option value="239" data-select2-id="26">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ACCUMULATED DEPRECIATION
                                    </option>
                                    <option value="240" data-select2-id="27">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; WORK IN PROGRESS (WIP
                                        CONSTRUCTION)</option>
                                    <option value="241" data-select2-id="28">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; PLANT AND MEDICAL EQUIPMENT
                                    </option>
                                    <option value="242" data-select2-id="29">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Building</option>
                                    <option value="" disabled="" style="font-weight:bolder"
                                        data-select2-id="30">Expense</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="31">&nbsp;&nbsp;&nbsp;&nbsp; Fixed Expense</option>
                                    <option value="174" data-select2-id="32">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Travel Expense</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="33">&nbsp;&nbsp;&nbsp;&nbsp; Administrative Expense</option>
                                    <option value="204" data-select2-id="34">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Salaries, Wages &amp; Other
                                        Benefits</option>
                                    <option value="205" data-select2-id="35">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Advertisement &amp; Marketing
                                        Expense</option>
                                    <option value="206" data-select2-id="36">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rent, Rates &amp; Taxes
                                    </option>
                                    <option value="207" data-select2-id="37">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Repair &amp; Maintenance
                                    </option>
                                    <option value="208" data-select2-id="38">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Printing, Postages &amp;
                                        Stationery Expenses</option>
                                    <option value="209" data-select2-id="39">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Communication &amp; Utilities
                                    </option>
                                    <option value="210" data-select2-id="40">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Legal Professional &amp;
                                        Charges</option>
                                    <option value="211" data-select2-id="41">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Depreciation &amp; Amortization
                                        Expenses</option>
                                    <option value="213" data-select2-id="42">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Entertainment Expenses</option>
                                    <option value="214" data-select2-id="43">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Petrol, Oil &amp; Lubricants
                                    </option>
                                    <option value="215" data-select2-id="44">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Travelling &amp; Conveyance
                                        Expenses</option>
                                    <option value="216" data-select2-id="45">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Miscellaneous Expenses</option>
                                    <option value="217" data-select2-id="46">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Interest, Expense &amp; Bank
                                        Charges</option>
                                    <option value="218" data-select2-id="47">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Penalties &amp; Fines</option>
                                    <option value="219" data-select2-id="48">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Taxation Expenses</option>
                                    <option value="220" data-select2-id="49">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Trade Payable</option>
                                    <option value="225" data-select2-id="50">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; STAFF MEDICAL EXPENSES</option>
                                    <option value="226" data-select2-id="51">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; INCENTIVE SALES STAFF</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="52">&nbsp;&nbsp;&nbsp;&nbsp; OPERATING EXPENSES</option>
                                    <option value="227" data-select2-id="53">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; FUND RAISING EXPENSES</option>
                                    <option value="228" data-select2-id="54">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; COMPUTER EXPENSE (OPERATING
                                        EXP)</option>
                                    <option value="229" data-select2-id="55">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; OPD EXPENSES</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="56">&nbsp;&nbsp;&nbsp;&nbsp; EXPENSES</option>
                                    <option value="" disabled="" style="font-weight:bolder"
                                        data-select2-id="57">Liability</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="58">&nbsp;&nbsp;&nbsp;&nbsp; NON-CURRENT LIABILITIES</option>
                                    <option value="245" data-select2-id="59">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; LONG TERM LOAN</option>
                                    <option value="248" data-select2-id="60">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Delt</option>
                                    <option value="249" data-select2-id="61">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Delt</option>
                                    <option value="250" data-select2-id="62">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Delt</option>
                                    <option value="251" data-select2-id="63">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="64">&nbsp;&nbsp;&nbsp;&nbsp; CURRENT LIABILITIES</option>
                                    <option value="212" data-select2-id="65">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Employee Contribution Liability
                                    </option>
                                    <option value="230" data-select2-id="66">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Payable to Supplier</option>
                                    <option value="231" data-select2-id="67">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Employer Contribution Liability
                                    </option>
                                    <option value="246" data-select2-id="68">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Utility Payable</option>
                                    <option value="247" data-select2-id="69">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SHORT TERM LOAN</option>
                                    <option value="252" data-select2-id="70">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; STAFF SALARY INCOME TAX
                                    </option>
                                    <option value="253" data-select2-id="71">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; STAFF PERFORMANCE ALLOWANCE
                                        PAYABLE</option>
                                    <option value="" disabled="" style="font-weight:bolder"
                                        data-select2-id="72">Income</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="73">&nbsp;&nbsp;&nbsp;&nbsp; Retained Earning</option>
                                    <option value="173" data-select2-id="74">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Offices</option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="75">&nbsp;&nbsp;&nbsp;&nbsp; GRANTS &amp; DONATIONS</option>
                                    <option value="254" data-select2-id="76">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATIONS FROM BIN QUTAB FAMILY
                                    </option>
                                    <option value="255" data-select2-id="77">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATIONS FROM OTHER DONORS
                                    </option>
                                    <option value="256" data-select2-id="78">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATION FROM OTHER DONORS FOR
                                        FLOOD</option>
                                    <option value="257" data-select2-id="79">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATION FROM BIN QUTAB GROUP
                                    </option>
                                    <option value="258" data-select2-id="80">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATION FROM BIN QUTAB FAMILY
                                        FOR FLOOD</option>
                                    <option value="259" data-select2-id="81">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; FUND RAISING EVENTS</option>
                                    <option value="260" data-select2-id="82">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DONATIONS FROM OTHERS</option>
                                    <option value="263" data-select2-id="83">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Donation/Zakat/Sadqah/Inkind
                                    </option>
                                    <option disabled="" value="" style="font-weight:bolder"
                                        data-select2-id="84">&nbsp;&nbsp;&nbsp;&nbsp; OTHER INCOME</option>
                                    <option value="261" data-select2-id="85">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; MISC INCOME</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="" class="form-label">Debit Account</label>
                                <input type="text" name="name" id="modal_short" class="form-control"
                                    placeholder="Type Name" aria-describedby="helpId" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" id="kt_modal_new_target_cancel" class="btn btn-light me-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button data-bs-dismiss="modal" type="submit" id="kt_modal_new_address_submit"
                        class="btn btn-primary">

                        <span class="indicator-label">Create</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
