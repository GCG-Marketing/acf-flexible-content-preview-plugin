jQuery(document).ready(function ($) {
  // Add new layout
  $("#add-layout").on("click", function (e) {
    e.preventDefault();
    var index = $("#acf-layouts-wrapper tr").length;
    var newRow =
      `
            <tr>
                <td><input type="text" name="acf_flexible_preview_images[` +
      index +
      `][layout_name]" value="" /></td>
                <td>
                    <input type="text" class="acf-image-url" name="acf_flexible_preview_images[` +
      index +
      `][image_url]" value="" />
                    <input type="button" class="button acf-image-upload" value="Upload Image" />
                </td>
                <td><a href="#" class="button acf-remove-layout">Remove</a></td>
            </tr>
        `;
    $("#acf-layouts-wrapper").append(newRow);
  });

  // Remove layout
  $(document).on("click", ".acf-remove-layout", function (e) {
    e.preventDefault();
    $(this).closest("tr").remove();
  });

  // Handle media upload
  $(document).on("click", ".acf-image-upload", function (e) {
    e.preventDefault();

    var button = $(this);
    var target = button.prev(".acf-image-url");

    var frame = wp.media({
      title: "Select or Upload Image",
      button: {
        text: "Use this image",
      },
      multiple: false,
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      target.val(attachment.url); // Set the selected image URL to the input field
    });

    frame.open();
  });
});
