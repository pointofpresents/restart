if($("#idTime").length) {
    var time = $("#idTime").data("time");
    var timezone = $("#idTime").data("zone");
    var updateTime = function() {
        $("#idTime").text(moment.unix(time).tz(timezone).format('HH:mm:ss z'));
        time = time + 1;
    };

    setInterval(updateTime,1000);
}

$("#selectall").on("click", function() {
    $("#xtnlist option").attr("selected", true);
});

$("input[name=enable_schedule]").on("change", function() {
    $("#schedtime").prop("disabled", !Boolean(parseInt(this.value)));
})
