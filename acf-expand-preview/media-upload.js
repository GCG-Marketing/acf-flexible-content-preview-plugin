jQuery(document).ready(function ($) {
  // Add new layout
  $("#add-layout").on("click", function (e) {
    e.preventDefault();
    var index = $("#acf-layouts-wrapper tr").length;
    var newRow =
      `
          <tr>
              <td><input type="text" name="acf_flexible_preview_media[` +
      index +
      `][layout_name]" value="" /></td>
              <td>
                  <input type="text" class="acf-image-url" name="acf_flexible_preview_media[` +
      index +
      `][image_url]" value="" />
                  <input type="button" class="button acf-image-upload" value="Upload Image" />
              </td>
              <td>
                  <input type="text" class="acf-video-url" name="acf_flexible_preview_media[` +
      index +
      `][video_url]" value="" />
                  <input type="button" class="button acf-video-upload" value="Upload Video" />
              </td>
              <td>
                  <input type="radio" name="acf_flexible_preview_media[` +
      index +
      `][media_type]" value="image" checked> Image
                  <br />
                  <input type="radio" name="acf_flexible_preview_media[` +
      index +
      `][media_type]" value="video"> Video
              </td>
              <td><a href="#" class="button acf-remove-layout">Remove</a></td>
          </tr>
      `;
    $("#acf-layouts-wrapper").append(newRow);
  });

  // Handle media upload for images
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

  // Handle media upload for videos
  $(document).on("click", ".acf-video-upload", function (e) {
    e.preventDefault();

    var button = $(this);
    var target = button.prev(".acf-video-url");

    var frame = wp.media({
      title: "Select or Upload Video",
      button: {
        text: "Use this video",
      },
      multiple: false,
      library: {
        type: "video",
      },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      target.val(attachment.url); // Set the selected video URL to the input field
    });

    frame.open();
  });

  // Remove layout row
  $(document).on("click", ".acf-remove-layout", function (e) {
    e.preventDefault();
    $(this).closest("tr").remove();
  });
});
