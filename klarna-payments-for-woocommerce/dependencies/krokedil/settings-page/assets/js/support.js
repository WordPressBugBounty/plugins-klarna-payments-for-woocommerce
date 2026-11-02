
'use strict';

jQuery(function ($) {
    const krokedil_support = {
      params: {
        beaconId: "",
      },
      init: function () {
        this.loadParams();
        this.initBeacon();
        this.setBeaconConfig();
        this.prefillBeacon();
        this.bindEvents();
      },

      loadParams: function () {
        this.params = krokedil_support_params;
      },

      setBeaconConfig: function () {
        if (typeof window.Beacon === "undefined") {
          return;
        }

        window.Beacon("config", {
          messaging: {
            contactForm: {
              allowAttachments: true,
            },
          },
        });
      },

      prefillBeacon: function () {
        if (typeof window.Beacon === "undefined") {
          return;
        }


        const systemReport = this.generateReport();
        if (!systemReport) {
          return;
        }

        const systemReportFile = new File([systemReport], "system-report.txt", {
          type: "text/plain",
          lastModified: Date.now(),
        });

        window.Beacon("prefill", {
          attachments: [
            systemReportFile
          ],
          fields: [
            {
              id: 44024,
              value: 228518,
            },
          ],
        });
      },

      initBeacon: function () {
        if (typeof window.Beacon === "undefined") {
          return;
        }

        window.Beacon("init", this.params.beaconId);
      },

      // Port of WooCommerce's generateReport (assets/js/admin/system-status.js), run on the hidden status report.
      generateReport: function () {
        let report = "";

        $("#krokedil-status-report .wc_status_table thead, #krokedil-status-report .wc_status_table tbody").each(function () {
          if ($(this).is("thead")) {
            const label = $(this).find("th:eq(0)").data("exportLabel") || $(this).text();
            report = report + "\n### " + label.trim() + " ###\n\n";
            return;
          }

          $("tr", $(this)).each(function () {
            const label = $(this).find("td:eq(0)").data("exportLabel") || $(this).find("td:eq(0)").text();
            const the_name = label.trim().replace(/(<([^>]+)>)/gi, ""); // Remove HTML.

            const $value_html = $(this).find("td:eq(2)").clone();
            $value_html.find(".private").remove();
            $value_html.find(".dashicons-yes").replaceWith("&#10004;");
            $value_html.find(".dashicons-no-alt, .dashicons-warning").replaceWith("&#10060;");

            let the_value = $value_html.text().trim();
            const value_array = the_value.split(", ");

            // Put each item of a comma separated list (e.g. plugins) on its own line.
            if (value_array.length > 1) {
              the_value = value_array.join("\n") + "\n";
            }

            if (the_name || the_value) {
              report = report + the_name + ": " + the_value + "\n";
            } else {
              report = report + "\n";
            }
          });
        });

        return report;
      },

      bindEvents: function () {
        // Open the support widget.
        $(".support-button").on("click", function () {
          window.Beacon("toggle");
        });
      },
    };

    krokedil_support.init();
});
