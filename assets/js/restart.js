if($("#idTime").length && typeof moment !== "undefined") {
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
    $(".scheduler").prop("disabled", !Boolean(parseInt(this.value)));
    $("#schedtime:enabled").focus();
});

$("#schedmonth").on("change", function() {
    var sel = $("#schedmonth");
    var months = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    var days = months[sel.val()];
    $("#schedday option").each(function(i, el) {
        el = $(el);
        el.prop("disabled", (el.val() > days));
    });
    if ($("#schedday option:selected").prop("disabled")) {
        $("#schedday").val(days).prop("selectedIndex", days);
    }
});

$("#pending_restart_grid").on("load-success.bs.table", function(e) {
    $("a.deleter").on("click", function(e) {
        e.preventDefault();
        if (confirm(_("Are you sure you wish to delete this job?"))) {
            $.get(this.href, function () {
                $("#pending_restart_grid").bootstrapTable("refresh", {silent: true});
            });
        }
    });
});

var Restart = {
    actionLinkFormatter: function (value, row, index) {
        var url = "ajax.php?module=restart&command=deleteJob&itemid=" + encodeURIComponent(row.jobname);
        var link = $("<a>")
            .attr("href", url)
            .addClass("deleter")
            .append($("<i>").addClass("fa").addClass("fa-trash"));
        return link.prop("outerHTML");
    }
};