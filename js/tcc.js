var formatTCC_service = function(col, row){
    
    var cell = $('td:eq('+col+')', row),
        value = cell.text();
    $(cell).html('<span title="'+value+'">'+i18n.t('tcc_jamf.'+value.replace("kTCCServiceSystemPolicy","").replace("kTCCService","")).replace("tcc_jamf.","")+'</span>')    
};

var formatTCCYesNo = function(col, row){
    
    var cell = $('td:eq('+col+')', row),
        value = cell.text()
    value = value == '1' ? '<span class="label label-danger">'+i18n.t('yes')+'</span>' :
        (value === '0' ? '<span class="label label-success">'+i18n.t('no')+'</span>' : '')
    cell.html(value)
};