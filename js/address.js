document.addEventListener("DOMContentLoaded", function () {
  var searchInput1 = document?.querySelector(".suggestaddr");
  var suggestionbox = document?.querySelector(".suggestionbox");

  var selectedLat = null;
  var selectedLng = null;

  const locations = [];

  function showlist() {
    if (!locations.length) {
      const query = searchInput1.value;
      fetch(`autocomplete_by_yandex.php?query=${encodeURIComponent(query)}`)
        .then((res) => res.json())
        .then((res) => {
          const l = res[0];
          console.log(res);

          fetch(
            `https://fitokrama.by/delivery_methods.php?lat=${l.lat}&lng=${l.lng}&address=${l.address}`
          )
            .then((res) => res.json())
            .then((res) => {
              document.querySelector(".placemarks").style.display = "flex";
              document.querySelector(".placemarks-list").style.display = "flex";
            });
        });
      return;
    }

    document.querySelector(".placemarks").style.display = "flex";
    document.querySelector(".placemarks-list").style.display = "flex";
  }

  function search() {
    var query = searchInput1.value;

    if (query.length > 2) {
      fetch(`autocomplete_by_yandex.php?query=${encodeURIComponent(query)}`)
        .then((response) => response.json())
        .then((data) => {
          suggestionbox.innerHTML = "";
          suggestionbox.style.display = "block";

          data.forEach(function (item) {
            var suggestionItem = document.createElement("div");
            suggestionItem.classList.add("suggestionItem");
            suggestionItem.textContent = item.address;
            suggestionItem.dataset.lat = item.lat;
            suggestionItem.dataset.lng = item.lng;

            suggestionItem.addEventListener("click", function () {
              searchInput1.value = item.address;
              selectedLat = item.lat;
              selectedLng = item.lng;

              document.querySelector("#client_address").value = item.address;
              document.querySelector("#waiting").style.display = "flex";

              fetch(
                `https://fitokrama.by/cart_edit.php?lat=${selectedLat}&lng=${selectedLng}&address=${item.address}`
              )
                .then((response) => response.json())
                .then((data) => {
                  document.querySelector(`.totalPriceKop`).innerHTML =
                    res.sum_kop;
                  document.querySelector(`.totalPriceRub`).innerHTML =
                    res.sum_rub;
                })
                .catch((error) => console.error(error))
                .finally(() => window.location.reload());

              suggestionbox.style.display = "none";
            });
            suggestionbox.appendChild(suggestionItem);
          });
        })
        .catch((error) => {
          console.error("Ошибка:", error);
        });
    } else {
      suggestionbox.style.display = "none";
    }
  }

  if (searchInput1) searchInput1?.addEventListener("input", search);
  if (searchInput1) searchInput1?.addEventListener("paste", search);

  (() => {
    document.querySelector("#waiting").style.display = "flex";

    fetch("https://fitokrama.by/cart_edit.php")
      .then((response) => response.json())
      .then((response) => {
        checkPhoneAndEmail(response);

        if (response.client_email_nochange_text) {
          const emailinp = document.querySelector("#client_email");
          emailinp.addEventListener("input", () => {
            emailinp.value = response.client_email;
            emailinp.blur();
            document.querySelector("#universalConfirm").style.display = "flex";

            document.querySelector(".msg").innerHTML =
              response.client_email_nochange_text;
          });
        }

        if (response.client_phone_nochange_text) {
          const phoneinp = document.querySelector("#client_phone");
          phoneinp.addEventListener("input", () => {
            phoneinp.value = response.client_phone;
            phoneinp.blur();
            document.querySelector("#universalConfirm").style.display = "flex";

            document.querySelector(".msg").innerHTML =
              response.client_phone_nochange_text;
          });
        }
      })
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );

    if (document.querySelector("#client_email").value == "") {
      const ENC = document.querySelector(".ENC");
      ENC.style.backgroundColor = "#ccc";
      ENC.style.border = "none";
      ENC.disabled = true;
    }

    if (document.querySelector("#client_phone").value == "") {
      const PNC = document.querySelector(".PNC");
      PNC.style.backgroundColor = "#ccc";
      PNC.style.border = "none";
      PNC.disabled = true;
    }
  })();

  document.addEventListener("keypress", (e) => {
    if (e.key == "Escape") {
      closeModal("deliveryModal");
      closeConfirmDeliveryMethodModal();
    }
  });

  window.showlist = showlist;
});
