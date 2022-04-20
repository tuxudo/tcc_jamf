<div id="tcc_jamf-tab"></div>
<h2 data-i18n="tcc_jamf.tcc"></h2>

<div id="tcc_jamf-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
	$.getJSON(appUrl + '/module/tcc_jamf/get_tab_data/' + serialNumber, function(data){
        
        // Check if we have data
        if(!data[0]){
            $('#tcc_jamf-msg').text(i18n.t('no_data'));
            $('#tcc_jamf-header').removeClass('hide');

        } else {

            // Hide
            $('#tcc_jamf-msg').text('');
            $('#tcc_jamf-view').removeClass('hide');

            var skipThese = ['id','serial_number','client'];
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    // Skip skipThese
                    if(skipThese.indexOf(prop) == -1){
                        // Do nothing for empty values to blank them
                        if (d[prop] == '' || d[prop] == null){
                            rows = rows

                        // Format boolean
                        } else if((prop == 'allowed') && d[prop] == 1){
                            rows = rows + '<tr><th>'+i18n.t('tcc_jamf.'+prop)+'</th><td><span class="label label-danger">'+i18n.t('yes')+'</span></td></tr>';
                        } else if((prop == 'allowed') && d[prop] == 0){
                            rows = rows + '<tr><th>'+i18n.t('tcc_jamf.'+prop)+'</th><td><span class="label label-success">'+i18n.t('no')+'</span></td></tr>';

                        // Format date
                        } else if(prop == "last_modified" && d[prop] > 0){
                            var date = new Date(d[prop] * 1000);
                            rows = rows + '<tr><th>'+i18n.t('tcc_jamf.'+prop)+'</th><td><span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span></td></tr>';

                        // Clean up service
                        } else if(prop == 'service'){
                            
                            var service = d[prop].replace("kTCCServiceSystemPolicy","").replace("kTCCService","")
                            rows = rows + '<tr><th>'+i18n.t('tcc_jamf.'+prop)+'</th><td>'+i18n.t('tcc_jamf.'+service).replace("tcc_jamf.","")+'</td></tr>';

                        // Else, build out rows from entries
                        } else {
                            rows = rows + '<tr><th>'+i18n.t('tcc_jamf.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                        }
                    }
                }

                if (d.allowed == 1){
                    $('#tcc_jamf-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-unlock'))
                            .append(' '+d.client))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                } else {
                    $('#tcc_jamf-tab')
                        .append($('<h4>')
                            .append($('<i>')
                                .addClass('fa fa-lock'))
                            .append(' '+d.client))
                        .append($('<div style="max-width:550px;">')
                            .append($('<table>')
                                .addClass('table table-striped table-condensed')
                                .append($('<tbody>')
                                    .append(rows))))
                }
            })
        }
	});
});
</script>
