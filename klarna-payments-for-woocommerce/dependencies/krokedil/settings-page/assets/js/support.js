
'use strict';

jQuery(function ($) {
    const krokedil_support = {
      params: {
        beaconId: "",
        systemReport: {},
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

        if (!this.params.systemReport) {
          return;
        }

        const systemReportFile = this.objToFile(
          this.params.systemReport,
          "system-report.json"
        );

        window.Beacon("prefill", {
          attachments: [
            {
              attachmentType: systemReportFile.type,
              attachmentFileName: systemReportFile.name,
              attachmentFileSize: systemReportFile.size,
              attachmentFileObject: systemReportFile,
            },
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

      objToFile: function (obj, fileName) {
        const stringVal = JSON.stringify(obj);

        return new File([stringVal], fileName, {
          type: "application/json",
          lastModified: Date.now(),
        });
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
