$(document).ready(function () {
    function loadProjects() {
        $("#projectTable").load("fetch_projects.php");
    }

    loadProjects();

    $("#addProjectBtn").click(function () {
        $("#projectModal").show();
        $("#projectForm")[0].reset();
        $("#project_id").val('');
    });

    $("#projectForm").submit(function (e) {
        e.preventDefault();
        $.post("projects.php", $(this).serialize() + "&action=" + ($("#project_id").val() ? "update" : "create"), function () {
            $("#projectModal").hide();
            loadProjects();
        });
    });

    $(document).on("click", ".editBtn", function () {
        let id = $(this).data("id");
        $.post("fetch_projects.php", { id: id }, function (data) {
            let project = JSON.parse(data);
            $("#project_id").val(project.id);
            $("#project_name").val(project.project_name);
            $("#description").val(project.description);
            $("#status").val(project.status);
            $("#total_cost").val(project.total_cost);
            $("#start_date").val(project.start_date);
            $("#completion_date").val(project.completion_date);
            $("#projectModal").show();
        });
    });

    $(document).on("click", ".deleteBtn", function () {
        if (confirm("Are you sure?")) {
            let id = $(this).data("id");
            $.post("function.php", { id: id, action: "delete" }, function () {
                loadProjects();
            });
        }
    });
});
