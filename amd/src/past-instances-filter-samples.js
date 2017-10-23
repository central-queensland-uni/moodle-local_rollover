define(['core/ajax', 'jquery'], function (ajax, $) {
    function initialise() {
        $('#update_samples').on('click', updateUI);
        updateUI();
    }

    function getRegEx() {
        return document.getElementById('id_regex').value;
    }

    function updateUI() {
        var args = {regex: getRegEx()};
        var webservices = [
            {methodname: 'local_rollover_regex_filter_get_sample_matches_by_regex', args: args}
        ];
        window.console.log(webservices);
        var promises = ajax.call(webservices);
        var promise = promises[0];
        promise.done(function (response) {
            window.console.log(response);
        });
        promise.fail(function (response) {
            window.console.error(response);
        });
    }

    return {
        initialise: initialise
    };
});
