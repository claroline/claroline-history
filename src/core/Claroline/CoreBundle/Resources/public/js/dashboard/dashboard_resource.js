(function () {

    var param = 'param';
    function filterCallBack(data, parameter) {
        console.debug(data);
    };
    function resetCallBack(parameter) {
        alert(parameter);
    };

    ClaroFilter.build(
        $('#div_filter'),
        'cr',
        function(data){filterCallBack(data, param)},
        function(){resetCallBack(param)}
    );

    ClaroResourceManager.init($('#dr-resources-content'),
        'cr', $('#dr-resources-back'),
        $('#dr-div-form'),
        $('#dr-select-creation'),
        $('#dr-submit-select'),
        $('#dr-download-button'),
        $('#dr-cut-button'),
        $('#dr-copy-button'),
        $('#dr-paste-button'),
        $('#dr-close-button'),
        $('#dr-is-flat')
    );
})();