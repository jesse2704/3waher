import $ from "jquery";
import urlBuilder from "mage/url";
export default function (config) {
  var module = {
    init: function () {
      this.cacheDom();
      this.bindEvents();
      this.listenForFacebookLink();
    },
    cacheDom: function () {
      this.$result = $("#result");
      this.$dropdownToggle = $(".dropdown-toggle");
      this.$photoAnalyzeContainer = $(".photo-analyze-container");
      this.$analyzeButton = $(".analyze-button");
    },
    bindEvents: function () {
      this.$dropdownToggle.on("click", this.toggleContainer.bind(this));
      this.$analyzeButton.on("click", this.analyzeFacebookProfilePic.bind(this));
    },
    listenForFacebookLink: function () {
      $(document).on("facebookLinked", (function (event, data) {
        console.log("Facebook linked event received");
      }).bind(this));
    },
    toggleContainer: function () {
      if (this.$photoAnalyzeContainer.is(":hidden")) {
        this.$photoAnalyzeContainer.show();
        this.$dropdownToggle.text("Hide Personalization Tool");
      } else {
        this.$photoAnalyzeContainer.hide();
        this.$dropdownToggle.text("Personalize Your Experience");
      }
    },
    analyzeFacebookProfilePic: async function () {
      this.$result.html("Loading...");
      try {
        const base64Image = await this.getImageBase64();
        if (!base64Image) return;
        const data = await this.callBetafaceAPI(base64Image);
        const allTags = this.extractTags(data);
        if (allTags.length > 0) {
          await this.sendTagsToMagento(allTags);
        }
        this.$result.html(`<pre>${JSON.stringify(data, null, 2)}</pre>`);
      } catch (error) {
        console.error("Error:", error);
        this.$result.html("An error occurred while analyzing the image.");
      }
    },
    getImageBase64: async function () {
      const imageInput = document.getElementById("file");
      const facebookUrl = config.facebookProfilePicUrl;
      if (imageInput.files[0]) {
        const file = imageInput.files[0];
        if (!["image/jpeg", "image/png"].includes(file.type)) {
          this.$result.html("Please upload a valid image (JPG or PNG).");
          return null;
        }
        if (file.size > 2 * 1024 * 1024) {
          this.$result.html("File size must be less than 2MB.");
          return null;
        }
        return this.readFileAsBase64(file);
      } else if (facebookUrl) {
        const response = await fetch(facebookUrl);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const blob = await response.blob();
        const file = new File([blob], "profile_picture.jpg", {
          type: blob.type
        });
        return this.readFileAsBase64(file);
      } else {
        this.$result.html("You did not link your Facebook, or the file you uploaded could not be read.");
        return null;
      }
    },
    callBetafaceAPI: async function (base64Image) {
      const apiResponse = await fetch("https://www.betafaceapi.com/api/v2/media", {
        method: "POST",
        headers: {
          "Authorization": "Basic d45fd466-51e2-4701-8da8-04351c872236",
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          api_key: "d45fd466-51e2-4701-8da8-04351c872236",
          file_base64: base64Image,
          detection_flags: "classifiers,extended"
        })
      });
      if (!apiResponse.ok) throw new Error(`HTTP error! status: ${apiResponse.status}`);
      return apiResponse.json();
    },
    extractTags: function (data) {
      let allTags = [];
      if (data.media && data.media.faces && Array.isArray(data.media.faces)) {
        data.media.faces.forEach(face => {
          if (face.tags && Array.isArray(face.tags)) {
            face.tags.forEach(tag => {
              allTags.push({
                name: tag.name,
                value: tag.value,
                confidence: tag.confidence
              });
            });
          } else {
            console.warn("No tags detected for this face");
          }
        });
      } else {
        console.error("No faces detected or invalid response structure");
      }
      return allTags;
    },
    sendTagsToMagento: async function (tags) {
      console.log("Tags before sending:", tags);
      var dataToSend = JSON.stringify({
        tags: tags
      });
      console.log("Stringified data:", dataToSend);
      try {
        const response = await $.ajax({
          url: urlBuilder.build("fotoanalyzetool/betaface/savebetafacedata"),
          method: "POST",
          data: dataToSend,
          contentType: "application/json",
          dataType: "json"
        });
        console.log("Success Response:", response);
        return response;
      } catch (error) {
        console.error("Error:", error);
        console.log("Status:", error.status);
        console.log("Response Text:", error.responseText);
        throw error;
      }
    },
    readFileAsBase64: function (file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result.split(",")[1]);
        reader.onerror = error => reject(error);
        reader.readAsDataURL(file);
      });
    }
  };
  $(document).ready(module.init.bind(module));
  return module;
}
