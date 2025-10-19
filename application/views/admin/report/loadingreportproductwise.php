<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style>
    table {
      border-collapse: collapse;
      width: 100%;
      font-size: 14px;
    }

    th, td {
      border: 1px solid #000;
      padding: 6px;
      text-align: center;
    }

    th.category {
      background-color: #d0f0f0;
      font-weight: bold;
      
    }

    th.item {
      background-color: #f0f8ff;
    }
     .vertical-text {
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      text-align: center;
      vertical-align: middle;
    }
  </style>
<div class="content-wrapper">
    <section class="content-header">
        <?php echo $pagetitle; ?>
        <?php echo $breadcrumb; ?>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <form id="filterform">
                                <div class="col-md-2">
                                    <select class="form-control" name="newsalesperson" id="newsalesperson" >
                                        <option value="">--Select Salesperson--</option>
                                         <option value="all">--All--</option>
                                        <?php foreach ($salespersons AS $salesperson) { ?>
                                            <option value="<?php echo $salesperson->RepID ?>"><?php echo $salesperson->RepName ?></option>
                                        <?php } ?>
                                    </select>
                                    <!--  -->
                                </div>
                                
                              


<div class="col-md-2">
    <select class="form-control" name="route[]" id="route" multiple>
        <!-- options -->
    </select>
    <input type="hidden" name="route_ar" id="route_ar">
</div>

<div class="col-md-2">
    <select class="form-control" name="customer[]" id="customer" multiple>
        <!-- options -->
    </select>
     <input type="hidden" name="customer_ar" id="customer_ar">
</div>



                              <div class="input-daterange input-group" id="datepicker">
                                        <input type="hidden" class="form-control" name="startdate" id="startdate" >
                            
                                        <input type="hidden" class="form-control" name="enddate" id="enddate" >
                                    </div>
                                <div class="col-lg-3">
                                    <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> 
                                        <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-flat btn-success">Show</button>
                                </div>
                                 
                            </form>
                           <div class="col-md-2">
                                <button onclick="printdiv()" class="btn btn-flat btn-default">Print</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="printReport">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body table-responsive">
                   <table id="saletable" class="table table-bordered">
  <thead></thead>
  <tbody></tbody>
</table>


                    </div>
                </div>
            </div>
        </div>
        <!--print view modal-->
        <div id="salesbydateprint" class="modal fade bs-add-category-modal-lg" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- load data -->
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $('.input-daterange').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd'
    });

     $("#route").select2({
        placeholder: "Select a location"
    });
$("#customer").select2({
    placeholder: "Select a customer"
});
 var loc2 = [];
    $("#route").change(function() {
        loc2.length = 0;

        $("#customer :selected").each(function() {
            loc2.push($(this).val());
        });
        $("#customer_ar").val(JSON.stringify(loc2));
    });

    var loc = [];
    $("#route").change(function() {
        loc.length = 0;

        $("#route :selected").each(function() {
            loc.push($(this).val());
        });
        $("#route_ar").val(JSON.stringify(loc));
    });

    $('#newsalesperson').on('change', function() {
    var salespersonID = $(this).val();
    if (salespersonID != "0") {

        $.ajax({
            url: "<?php echo base_url(); ?>" + "admin/customer/findemploeeroute",
            method: 'POST',
            data: { salespersonID: salespersonID },
            dataType: 'json',
            success: function(response) {

                $('#route').empty();
                $('#route').append('<option value="0">-Select-</option>');

                var addedRoutes = new Set();

                $.each(response, function(index, routeID) {
                    if (!addedRoutes.has(routeID.route_id)) {
                        $('#route').append('<option value="' + routeID.route_id + '">' + routeID.route_name + '</option>');
                        addedRoutes.add(routeID.route_id);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching routes:', error);
            }
        });

    } else {
        $('#route').empty();
        $('#route').append('<option value="0">-Select-</option>');
    }
});

   


$('#filterform').submit(function (e) {
        e.preventDefault();
  
        $.ajax({
            type: 'POST',
            url: "loadloadingreportpro",
            data: $(this).serialize(),
            success: function (response) {
                console.log(response);
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (err) {
                        console.error("Invalid JSON response:", err);
                        alert("Invalid server response.");
                        return;
                    }
                }

                if (response.error) {
                    alert("Error: " + response.error);
                    return;
                }

                const { headers, rows } = response;
                const $thead = $('#saletable thead').empty();
                const $tbody = $('#saletable tbody').empty();

                let headerRow1 = `<tr><th rowspan="2" style="border: 2px solid black; writing-mode: vertical-rl; transform: rotate(180deg);">Bill No</th>`;
                let headerRow2 = '';

                headers.uniqueDescriptions.forEach(desc => {
                    const items = headers.descriptions[desc] || [];
                    headerRow1 += `<th style="border: 2px solid black; " colspan="${items.length}">${desc}</th>`;
                    items.forEach(item => {
                        headerRow2 += `<th style="border: 2px solid black; writing-mode: vertical-rl; transform: rotate(180deg);">${item}</th>`;
                    });
                });

                headerRow1 += `</tr>`;
                headerRow2 = `<tr>${headerRow2}</tr>`;
                $thead.append(headerRow1 + headerRow2);

                if (!rows.length) {
                    $tbody.append(`<tr><td colspan="100%" style="border: 2px solid black;  text-align:center;">No data found</td></tr>`);
                    return;
                }

                rows.forEach(rowData => {
                    let row = `<tr><td style="border: 2px solid black; ">${rowData.billNo}</td>`;
                    headers.uniqueDescriptions.forEach(desc => {
                        const items = headers.descriptions[desc] || [];
                        items.forEach(item => {
                            const qty = (rowData.quantities[desc] && rowData.quantities[desc][item])
                                ? rowData.quantities[desc][item]
                                : 0;
                            row += `<td style="border: 2px solid black; ">${qty}</td>`;
                        });
                    });
                    row += `</tr>`;
                    $tbody.append(row);
                });
                // Add total row
let totalRow = `<tr><td style="border: 2px solid black; "><strong>Total</strong></td>`;
headers.uniqueDescriptions.forEach(desc => {
    const items = headers.descriptions[desc] || [];
    items.forEach(item => {
        let total = 0;
        rows.forEach(rowData => {
            const qty = (rowData.quantities[desc] && rowData.quantities[desc][item])
                ? rowData.quantities[desc][item]
                : 0;
            total += qty;
        });
        totalRow += `<td style="border: 2px solid black; "><strong>${total}</strong></td>`;
    });
});
totalRow += `</tr>`;
$tbody.append(totalRow);

            },
            error: function (xhr, status, error) {
    console.error("AJAX error:");
    console.log("Status:", status);
    console.log("Error:", error);
    console.log("Response Text:", xhr.responseText);
    console.log("XHR Object:", xhr);
    
    alert("No Data found:\nStatus: " + status + "\nError: " + error + "\nResponse: " + xhr.responseText);
}

        });
    });







    function loadprint() {
        $('.modal-content').load('<?php echo base_url() ?>admin/report/', function (result) {
            $('#salesbydateprint').modal({show: true});
        });
    }
    function printdiv() {
        var datebalance = $("#enddate").val();
        $("#printReport").print({
            prepend:"<h3 style='text-align:center'>Daily Loading Report "+datebalance+"</h3><hr/>",
            title:'Daily Loading Report '+datebalance
        });
    }

      $(function() {

    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        $('#startdate').val(start.format('YYYY-MM-DD'));
        $('#enddate').val(end.format('YYYY-MM-DD'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

    });
    
</script>