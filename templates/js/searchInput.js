$("#searchInput").keyup(function () {
    // Split the current value of the filter textbox
    var data = this.value.split(" ");
    // Get the table rows
    var rows = $("#fbody").find("tr");
    if (this.value == "") {
        rows.show();
        return;
    }
    
    // Hide all the rows initially
    rows.hide();

    // Filter the rows; check each term in data
    rows.filter(function (i, v) {
        for (var d = 0; d < data.length; ++d) {
            if ($(this).is(":contains('" + data[d] + "')")) {
                return true;
            }
        }
        return false;
    })
    // Show the rows that match.
    .show();
}).focus(function () { // style the filter box
    this.value = "";
    $(this).css({
        "color": "black"
    });
    $(this).unbind('focus');
}).css({
    "color": "#C0C0C0"
});

// make contains case insensitive globally
// (if you prefer, create a new Contains or containsCI function)
$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});