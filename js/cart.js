document.addEventListener("DOMContentLoaded", function () {
  const apiKey = "YOUR_API_KEY";

  const mapContainer = document.getElementById("map");

  let map;

  function openPaymentModal() {
    const paymentModal = document.getElementById("paymentModal");

    paymentModal.style.display = "block";

    const closePaymentBtn = document.getElementById("closePaymentModal");

    closePaymentBtn.onclick = function () {
      paymentModal.style.display = "none";
    };
  }

  const mapAction = (res) => {
    if (!map) {
      ymaps.ready(() => {
        map = new ymaps.Map("map", {
          center: [res.centerCoordinates.lat, res.centerCoordinates.lng],

          zoom: 10,
        });

        addPlacemarksToMap(res);
      });
    } else {
      updateMap(res);
    }
  };

  function openDeliveryModal() {
    document.getElementById("deliveryModal").style.display = "block";

    let query = document.getElementById("client_address").value;

    console.log(query);

    fetch(`autocomplete_by_yandex.php?query=${encodeURIComponent(query)}`)
      .then((response) => response.json())
      .then((data) => {
        fetch(
          `https://fitokrama.by/delivery_methods.php?lat=${data[0].lat}&lng=${data[0].lng}&address=${data[0].address}`
        )
          .then((res) => res.json())
          .then((res) => {
            if (!map) {
              ymaps.ready(() => {
                map = new ymaps.Map("map", {
                  center: [
                    res.centerCoordinates.lat,
                    res.centerCoordinates.lng,
                  ],

                  zoom: 10,
                });

                addPlacemarksToMap(res);
              });
            } else {
              updateMap(res);
            }
          })
          .catch((error) => console.error(error));
      })
      .catch((error) => {
        console.error("Ошибка:", error);
      });
  }

  function addPlacemarksToMap(data) {
    let centerPlacemark = new ymaps.Placemark(
      [data.centerCoordinates.lat, data.centerCoordinates.lng],

      {
        balloonContent: "Центральная точка",

        iconCaption: "Центральная точка",
      },

      {
        preset: "islands#redDotIcon",
      }
    );

    map.geoObjects.add(centerPlacemark);

    let bounds = ymaps
      .geoQuery(
        data.locations.map((location) => {
          try {
            let placemark = new ymaps.Placemark(
              [location.lat, location.lng],
              {
                balloonContent: `

                            <div class="placemark">

                                <div class="placemark-logo">

                                    <img src="/logos/${location.logo}" alt="${location.name}">

                                </div>

                                <div class="placemark-content">

                                    <strong>${location.name}</strong>

                                    ${location.address}

                                    <span class="placemark-price total-price"><span>${location.price_rub},</span> <sup>${location.price_kop}</sup> &nbsp; руб.</span>

                                    <button class="select-button" style="z-index:999999" onclick="openConfirmDeliveryMethodModal('${location.name}', '${location.address}', '${location.price}',${location.id},'${location.partner_id}', '${location.lat}', '${location.lng}')">Выбрать</button>

                                </div>

                            </div>

                        `,

                iconCaption: location.name,
              },
              {
                iconLayout: "default#imageWithContent",

                iconImageHref: "/path/to/your/marker/icon.png",

                iconImageSize: [30, 42],

                iconImageOffset: [-15, -42],

                iconContentOffset: [15, 15],

                iconContentLayout: ymaps.templateLayoutFactory.createClass(`

                            <div class="placemark">

                                <div class="placemark-logo">

                                    <img src="/logos/${location.logo}" alt="${location.name}">

                                </div>

                                <div class="placemark-content">

                                    <strong>${location.name}</strong>

                                    ${location.address}

                                    <span class="placemark-price total-price"><span>${location.price_rub},</span> <sup>${location.price_kop}</sup> &nbsp; руб.</span>

                                    <button class="select-button" onclick="openConfirmDeliveryMethodModal('${location.name}', '${location.address}', '${location.price}', ${location.id},'${location.partner_id}', '${location.lat}', '${location.lng})">Выбрать</button>

                                </div>

                            </div>

                        `),
              }
            );

            map?.geoObjects?.add(placemark);

            return placemark;
          } catch (error) {
            console.error("Error adding placemark:", error, location);

            return null;
          }
        })
      )
      .addToMap(map)
      .getBounds();

    map.setBounds(bounds, {
      checkZoomRange: true,

      zoomMargin: [10, 10, 10, 10],
    });

    let minZoom = map.options.get("minZoom");

    map.setZoom(Math.min(map.getZoom(), minZoom));
  }

  function updateMap(data) {
    map?.geoObjects?.removeAll();

    addPlacemarksToMap(data);

    map.setCenter([data.centerCoordinates.lat, data.centerCoordinates.lng], 10);
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);

    modal.style.display = "none";
  }

  let deliveryId = "";
  let partnerId = "";
  let lat = "";
  let lng = "";
  let deliveryPrice = "";

  function openConfirmDeliveryMethodModal(
    name,
    address,
    price,
    id,
    partner_id,
    lattitude,
    longitude
  ) {
    const modal = document.getElementById("confirmDeliveryMethodModal");

    modal.style.display = "block";

    deliveryId = id;
    partnerId = partner_id;
    deliveryPrice = price;
    lat = lattitude;
    lng = longitude;

    document.getElementById(
      "selectedDeliveryMethod"
    ).innerText = `${name}, ${address}, ${price} руб.`;

    localStorage.setItem(
      "selectedDeliveryMethod",
      JSON.stringify({ name, address, price })
    );
  }

  function closeConfirmDeliveryMethodModal() {
    document.getElementById("confirmDeliveryMethodModal").style.display =
      "none";
  }

  function confirmDeliveryMethod() {
    closeConfirmDeliveryMethodModal();

    closeModal("deliveryModal");

    fetch("https://fitokrama.by/delivery_assign.php", {
      method: "POST",

      body: JSON.stringify({
        method_id: deliveryId,
        partner_id: partnerId,
        lat,
        lng,
        method_price: deliveryPrice,
      }),

      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        document.querySelector(`.price-small-total`).innerHTML =
          data.delivery_price_kop;
        document.querySelector(`.price-full-total`).innerHTML =
          data.delivery_price_rub;

          document.querySelector("#delivery_logo").src = `/logos/${data.logo}`;

          document.querySelector("#delivery_text").innerHTML = data.delivery_text;
      })
      .catch((error) => console.error("Error:", error));
  }

  const deletingItem = {};

  function changeQuantity(change, itemId, id, price) {
    const quantityInput = document.getElementById(`quantity-${itemId}`);

    let quantity = parseInt(quantityInput.value) + change;

    if (quantity < 1) {
      quantity = 1;

      document.querySelector("#deleteItem").style.display = "block";

      deletingItem.id = id;
      deletingItem.price = price;
      deletingItem.qty = 0;
      console.log(deletingItem);
    }

    fetch("https://fitokrama.by/cart_correct.php", {
      method: "POST",

      body: JSON.stringify({ goodart: id, qty: quantity, price: price }),

      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        data?.goods?.map((i, index) => {
          document.querySelector(`.price-full-${index + 1}`).innerHTML =
            i.good_sum_rub;

          document.querySelector(`.price-small-${index + 1}`).innerHTML =
            i.good_sum_kop;

          document.getElementById(`quantity-item${index + 1}`).value = i.qty;
        });
        document.querySelector(".totalPriceRub").innerHTML = data.sum_rub;
        document.querySelector(".totalPriceKop").innerHTML = data.sum_kop;

        document.querySelector(`.price-small-total`).innerHTML =
          data.delivery_price_kop;
        document.querySelector(`.price-full-total`).innerHTML =
          data.delivery_price_rub;
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  }

  function deleteItem() {
    fetch("https://fitokrama.by/cart_correct.php", {
      method: "POST",

      body: JSON.stringify({
        goodart: deletingItem.id,
        qty: deletingItem.qty,
        price: deletingItem.price,
      }),

      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        document.querySelector(".totalPriceRub").innerHTML = data.sum_rub;
        document.querySelector(".totalPriceKop").innerHTML = data.sum_kop;

        document.querySelector(`.price-small-total`).innerHTML =
          data.delivery_price_kop;
        document.querySelector(`.price-full-total`).innerHTML =
          data.delivery_price_rub;

        window.location.reload();
      })
      .catch((error) => {
        console.error("Error:", error);
      })
      .finally(
        () => (document.querySelector("#deleteItem").style.display = "none")
      );
  }

  const openConfirmPhone = () => {
    const phonetimerzero = document.querySelector(".phonetimerzero");

    phonetimerzero.innerHTML = "0:";

    let timer = 59;

    const interval = setInterval(() => {
      if (timer < 10) {
        document.querySelector(".phonetimerzero").innerHTML = "0:0";
      }
      document.querySelector(".phonetimer").innerHTML = timer;
      timer--;
    }, 1000);
    document.getElementById("confirmPhone").style.display = "block";

    fetch("https://fitokrama.by/confirm_phone_request.php")
      .then((res) => res.json())
      .then((res) => console.log(res));

      document.querySelector(".first-phone-code-input").focus();

    setTimeout(() => {
      const sendbutton = document.querySelector(".sendCodeAgainPhone");
      document.querySelector(".phonetimerzero").style.display = "none";
      document.querySelector(".phonetimer").style.display = "none";
      sendbutton.classList.add("available");
      sendbutton.classList.add("availablePhone");
      clearInterval(interval);

      document
        .querySelector(".availablePhone")
        .addEventListener("click", () => {
          sendbutton.classList.remove("available");
          const phonetimer = document.querySelector(".phonetimer");

          phonetimerzero.style.display = "flex";
          phonetimerzero.innerHTML = "0:";

          phonetimer.style.display = "flex";
          phonetimer.innerHTML = 59;

          clearInterval(interval);
          openConfirmPhone();
        });
    }, 60000);
  };

  const buyItem = () => {
    fetch("https://fitokrama.by/cart_edit.php")
      .then((response) => response.json())
      .then((response) => {
        checkPhoneAndEmail(response);

        if (response.datetime_email_confirmed == null) {
          if (response.datetime_email_confirmed == null) {
            document.querySelector(".ENC").style.border = "2px solid red";
          }
        } else if (response.datetime_email_confirmed != null) {
          document.querySelector(".ENC").style.border = "none";
        }

        if (response.datetime_phone_confirmed == null) {
          document.querySelector(".PNC").style.border = "2px solid red";
        } else if (response.datetime_phone_confirmed != null) {
          document.querySelector(".PNC").style.border = "none";
        }

        if (response.client_email == "") {
          document.querySelector("#client_email").style.border =
            "2px solid red";
        } else if (response.client_email.length > 5) {
          document.querySelector("#client_email").style.border =
            "1px solid #ccc";
        }

        if (response.client_phone == "") {
          document.querySelector("#client_phone").style.border =
            "2px solid red";
        } else if (response.client_phone.length > 5) {
          document.querySelector("#client_phone").style.border =
            "1px solid #ccc";
        }
      });
  };

  const locations = [];

  function showlist() {
    document.querySelector("#map").style.display = "none";
    document.querySelector(".list").style.display = "none";
    document.querySelector(".map").style.display = "block";
    document.querySelector(".sort").style.display = "flex";
    document.querySelector(".placemarks-list").style.display = "flex";

    const address = document.getElementById("client_address");
    console.log(address);

    fetch(
      `autocomplete_by_yandex.php?query=${encodeURIComponent(address.value)}`
    )
      .then((response) => response.json())
      .then((data) => {
        fetch(
          `https://fitokrama.by/delivery_methods.php?lat=${data[0].lat}&lng=${data[0].lng}&address=${data[0].address}`
        )
          .then((res) => res.json())
          .then((res) => {
            const lc = res.locations;
            locations.push(...lc);
            setplacemarks(lc);
          })
          .catch((error) => console.error(error));
      })
      .catch((error) => {
        console.error("Ошибка:", error);
      });
  }

  const sortbyprice = (isexpencive) => {
    const lc = locations.sort((a, b) =>
      !isexpencive ? a.price - b.price : b.price - a.price
    );
    setplacemarks(lc);
  };

  const sortbyfar = (isfar) => {
    const lc = locations.sort((a, b) =>
      !isfar ? a.distance - b.distance : b.distance - a.distance
    );
    setplacemarks(lc);

    document.querySelector(".far").classList.toggle("active");
    document.querySelector(".notfar").classList.toggle("active");
  };

  const setplacemarks = (lc) => {
    document.querySelector(".placemarks-list").innerHTML = lc
      .map((l) => {
        l.name = l.name.replace(/"/g, "&quot;");
        return `
                 <div class="placemarks-list-item" onclick="openConfirmDeliveryMethodModal('${l.name}', '${l.address}', '${l.price}' ,${l.id}, '${l.partner_id}', '${l.lat}', '${l.lng}')">
                    <section class="placemark-section">
                      <img class="placemark-logo" src="./logos/${l.logo}" alt="" />
                      <h5 class="placemark-list-price total-price"><span>${l.price_rub},</span>
                        <sup>${l.price_kop}</sup> руб.
                        </h5>
                      <span class="placemark-distance">${l.distance} м</span>
                      <p class="placemark-walking-time">${l.walkingTime} мин</p>
                    </section>
                    <h2 class="placemark-name">${l.name}</h2>
                    <h3 class="placemark-availability">${l.availability}</h3>
                  </div>
                `;
      })
      .join(" ");
  };

  function showmap() {
    document.querySelector("#map").style.display = "block";
    document.querySelector(".list").style.display = "block";
    document.querySelector(".map").style.display = "none";
    document.querySelector(".sort").style.display = "none";
    document.querySelector(".placemarks-list").style.display = "none";
  }

  function setInputValue() {
    const delivery = document.querySelector("#delivery-address");
    const address = document.querySelector("#client_address");
    const modaladdress = document.querySelector("#client_address");

    modaladdress.value = address.value;
    delivery.value = address.value;
  }

  function inc(art) {
    let input = document.querySelector(".quantity-input-" + art);

    input.value = parseInt(input.value) + 1;
  }

  function dec(art) {
    let input = document.querySelector(".quantity-input-" + art);

    if (parseInt(input.value) > 1) {
      input.value = parseInt(input.value) - 1;
    }
  }

  window.openDeliveryModal = openDeliveryModal;

  window.openPaymentModal = openPaymentModal;

  window.closeModal = closeModal;

  window.changeQuantity = changeQuantity;

  window.openConfirmDeliveryMethodModal = openConfirmDeliveryMethodModal;

  window.closeConfirmDeliveryMethodModal = closeConfirmDeliveryMethodModal;

  window.confirmDeliveryMethod = confirmDeliveryMethod;

  window.deleteItem = deleteItem;

  window.updateMap = updateMap;

  window.mapAction = mapAction;

  window.buyItem = buyItem;

  window.openConfirmPhone = openConfirmPhone;

  window.showlist = showlist;

  window.showmap = showmap;

  window.sortbyprice = sortbyprice;

  window.sortbyfar = sortbyfar;

  window.inc = inc;

  window.dec = dec;

  window.setInputValue = setInputValue;

  window.locations = locations;
});
