// Wait for DOM to fully load
$(document.ready || function () {
    // 1. Auto dismiss bootstrap alerts after 4 seconds
    setTimeout(function () {
        $(".alert").fadeOut("slow", function () {
            $(this).remove();
        });
    }, 4000);

    // 2. Global confirmation dialog for delete buttons
    $(document).on("click", ".btn-delete, .delete-btn, [href*='delete']", function (e) {
        if (!confirm("តើអ្នកពិតជាចង់លុបទិន្នន័យនេះមែនទេ?")) {
            e.preventDefault();
        }
    });

    // 3. Sidebar toggle for mobile/responsive layout
    $("#sidebarToggle").on("click", function (e) {
        e.preventDefault();
        $("body").toggleClass("sb-sidenav-toggled");
    });
});