@extends('layout.base')

@section("custom_css")
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
<style>
    .select2-container--classic .select2-selection--multiple .select2-selection__choice {
        background-color: #5369f8;
        border: 1px solid #5369f8;
    }

    .select2-container--classic .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
</style>
@stop

@section('content')
<div class="container-fluid">

    <div class="row page-title align-items-center">
        <div class="col-sm-4 col-xl-6">
            <h4 class="mb-1 mt-0">Compose</h4>
        </div>
    </div>
    <!-- @php
        var_dump($response);
    @endphp -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4 float-sm-left">Compose Message</h3>
                    @include('partials.alert.message')

                    <div class="row col-12">
                        <form action="{{ route('broadcast.store') }}" method="POST" class="col-12">
                            @csrf
                            <div class="form-group">
                                <label>Store</label>
                                <select class="form-control col-12 jstags" name="store" id="store" required>
                                    <option value="" selected disabled>None selected</option>
                                    @foreach ($response as $index => $store )
                                    <option value="{{$store->_id}}">{{$store->store_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Send To</label>
                                <select class="form-control col-12" name="send_to" id="send_to" required>
                                    <option value="1"> All Customers</option>
                                    <option value="2"> Selected Customers</option>
                                </select>
                            </div>
                            <div class="form-group d-none" id='customersGroup'>
                                <label>Select Customer(s)</label>
                                <select class="form-control col-12 jstags" multiple  name="customer[]">

                                </select>
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <select class="form-control col-12" name="message" id="msgselect" required>
                                    <option value="" selected disabled>None selected</option>
                                    <option value="Happy new year!">Happy new year!</option>
                                    <option value="We are now open!">We are now open!</option>
                                    <option value="New stocks just arrived!">New stocks just arrived!</option>
                                    <option value="Happy new Month">Happy new Month</option>
                                    <option value="Thank you for shopping with">Thank you for shopping with US!</option>
                                    <option value="other">Custom Message</option>
                                </select>
                            </div>
                            <div class="form-group" id="txtarea">
                                <label for="exampleFormControlTextarea1">Your Custom Message</label>
                                <textarea class="form-control" name="_message" rows="3"></textarea>
                            </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Send &nbsp;<i class="fa fa-paper-plane my-float"></i>
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


<div class="card mt-0">
    <div class="card-header">
        <div class="">History: Broadcast Messages</div>
    </div>
    <div class="card-body">
        <div class="table-responsive table-data">
            <table id="debtReminders" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date Sent</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($transaction->debts as $index => $debt) --}}
                    <tr>
                        <td>
                            {{-- {{ $index + 1 }} --}}
                        </td>
                        <td>
                            {{-- {{ $debt->message }} --}}
                        </td>
                        <td><span class="badge badge-success">
                            {{-- {{ $debt->status }} --}}
                        </span></td>
                        <td>
                            {{-- {{ \Carbon\Carbon::parse($debt->createdAt)->diffForhumans() }} --}}
                        </td>
                        <td>
                            <a href="" data-toggle="modal"
                               
                                data-target="#ResendReminderModal" class="btn btn-primary btn-sm mt-2">
                                Resend
                            </a>
                        </td>
                    </tr>
                    {{-- Modal for resend reminder --}}
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section("javascript")
<script type="text/javascript"
    src="https://cdn.datatables.net/v/bs4/jq-3.3.1/jszip-2.5.0/dt-1.10.21/b-1.6.2/b-html5-1.6.2/datatables.min.js">
</script>
<script src="https://code.jquery.com/jquery-1.8.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<!-- App js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    jQuery(function($) {
        const token = "{{Cookie::get('api_token')}}"
        const host = "{{ env('API_URL', 'https://dev.api.customerpay.me') }}";

        $('select[name="store"]').on('change', function() {
            var storeID = $(this).val();
            var host = "{{ env('API_URL', 'https://dev.api.customerpay.me') }}";

            if (storeID) {
                $('select[name="customer[]"]').empty();
                jQuery.ajax({
                    url: host + "/store/" + encodeURI(storeID)
                    , type: "GET"
                    , dataType: "json"
                    , contentType: 'json'
                    , headers: {
                        'x-access-token': token
                    }
                    , success: function(data) {
                        var new_data = data.data.store.customers;
                        var i;
                        new_data.forEach(customer => {
                            $('select[name="customer[]"]').append('<option multiple value="' +
                                customer.phone_number + '">' +
                                customer.name + '</option>');
                        });
                    }
                });
            } else {
                $('select[name="store"]').empty();
            }
        });

        $('#send_to').change(function(){
            if ($(this).val() == '2') {
                $('#customersGroup').removeClass('d-none');
            } else {
                $('#customersGroup').addClass('d-none');
            }
        });

    });

</script>
<script>
    $("#txtarea").hide();
    $("#msgselect").change(function() {
        var val = $("#msgselect").val();
        if (val == "other") {
            $("#txtarea").show();
        } else {
            $("#txtarea").hide();
        }
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(".jstags").select2({
        theme: "classic",
        tags: true,
    });
</script>
@stop