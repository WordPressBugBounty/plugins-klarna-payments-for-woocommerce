jQuery(function ($) {
  const krokedil_metabox = {
    init: function () {
      $(document).on(
        "click",
        ".krokedil_wc__metabox_section_toggle",
        this.toggle
      );
    },

    toggle: function (e) {
      e.preventDefault();
      const $this = $(this);
      const $section = $this.closest(".krokedil_wc__metabox_section");
      const $content = $section.find(".krokedil_wc__metabox_section_content");

      $content.stop().slideToggle({
        duration: 150,
        easing: "linear",
      });
      $this.find(".dashicons").toggleClass("krokedil_wc__metabox_open");
    },
  };

  krokedil_metabox.init();
});
