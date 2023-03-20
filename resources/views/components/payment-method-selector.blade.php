<div class="row mw-500px mb-5" data-kt-buttons="true"
                            data-kt-buttons-target=".form-check-image, .form-check-input">

                            <div class="col-4">
                                <label class="form-check-image">
                                    <div class="form-check-wrapper">
                                        <img src="{{ asset('assets/media/icon/card.png') }}" />
                                    </div>
                                    <div class="form-check form-check-custom form-check-solid me-10">
                                        <input checked class="form-check-input" type="radio" name="payment_method"
                                            value="card" id="text_wow" />
                                        <div class="form-check-label">
                                            Card
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="form-check-image">
                                    <div class="form-check-wrapper">
                                        <img src="{{ asset('assets/media/icon/cash.png') }}" />
                                    </div>
                                    <div class="form-check form-check-custom form-check-solid me-10">
                                        <input class="form-check-input" type="radio"name="payment_method"
                                            value="cash" />
                                        <div class="form-check-label">
                                            Cash
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>