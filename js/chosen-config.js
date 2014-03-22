var config = {
    '.chosen-select': {},
    '.chosen-select-student': {width:"20%"},
}
for (var selector in config) {
    jQuery(selector).chosen(config[selector]);
}