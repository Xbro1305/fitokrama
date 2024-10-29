document.addEventListener("DOMContentLoaded", function () {
  let deliveryId = "";
  let partnerId = "";
  let lat = "";
  let lng = "";
  let deliveryPrice = "";
  let deliveryName = "";

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

    modal.style.display = "flex";

    deliveryId = id;
    partnerId = partner_id;
    deliveryPrice = price;
    lat = lattitude;
    lng = longitude;
    deliveryName = name;

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

    document.querySelector("#waiting").style.display = "flex";
    document.querySelector(".placemarks").style.display = "none";
    document.querySelector("#delivery_text").value = deliveryName;

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
      .then((res) => {
        document.querySelector(`.totalPriceKop`).innerHTML = res.sum_kop;
        document.querySelector(`.totalPriceRub`).innerHTML = res.sum_rub;
        console.log(res);

        document.querySelector(
          "#delivery_logo"
        ).src = `/logos/${res.delivery_logo}`;

        document.querySelector("#delivery_text").innerHTML = res.delivery_text;
      })
      .catch((error) => console.error("Error:", error))
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  }

  const deletingItem = {};

  function changeQuantity(change, itemId, id, price) {
    const quantityInput = document.getElementById(`quantity-${itemId}`);
    document.querySelector("#waiting").style.display = "flex";

    let quantity = parseInt(quantityInput.value) + change;

    if (quantity < 1) {
      quantity = 1;

      document.querySelector("#deleteItem").style.display = "flex";

      deletingItem.id = id;
      deletingItem.price = price;
      deletingItem.qty = 0;
      return;
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
        window.location.reload();
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
    document.querySelector("#waiting").style.display = "flex";

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
    document.getElementById("confirmPhone").style.display = "flex";

    fetch("https://fitokrama.by/confirm_phone_request.php").finally(
      () => (document.querySelector("#waiting").style.display = "none")
    );

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
          sendbutton.classList.remove("availablePhone");
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
    document.querySelector("#waiting").style.display = "flex";

    fetch("https://fitokrama.by/cart_finish.php")
      .then((res) => res.json())
      .then((res) => {
        if (res.error_text) {
          document.querySelector("#universalConfirm").style.display = "flex";
          document.querySelector(".msg").innerHTML = res.error_text;
          return;
        }
        window.location.href = res.redirect_url;
      })
      .catch((err) => {
        console.log(err);
      })
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  };

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

  window.changeQuantity = changeQuantity;

  window.openConfirmDeliveryMethodModal = openConfirmDeliveryMethodModal;

  window.closeConfirmDeliveryMethodModal = closeConfirmDeliveryMethodModal;

  window.confirmDeliveryMethod = confirmDeliveryMethod;

  window.deleteItem = deleteItem;

  window.buyItem = buyItem;

  window.openConfirmPhone = openConfirmPhone;

  window.inc = inc;

  window.dec = dec;

  window.setInputValue = setInputValue;
});
