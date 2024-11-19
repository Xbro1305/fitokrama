function showAddedToCartModal(art, price, quantity) {
  if (sessionId) {
    document.querySelector("#waiting").style.display = "flex";

    fetch("https://fitokrama.by/cart_correct.php", {
      method: "POST",
      body: JSON.stringify({
        goodart: art,
        price: price,
        qty: quantity,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        document.querySelector("#addedToCartModal").style.display = "flex";
        const cartCount = data.cart_count;
        document.querySelector(".modalMobMsg").innerHTML = cartCount;
        document.querySelector(".cartCountMob").innerHTML = cartCount;
        document.querySelector(".cartCountPc").innerHTML = cartCount;
        document.querySelector(".cartCountMob").style.display = "flex";
        document.querySelector(".cartCountPc").style.display = "flex";
      })
      .catch((error) => console.log(error))
      .finally(() => {
        document.querySelector("#waiting").style.display = "none";
      });
  } else {
    document.querySelector(".cookiesagainstpopup").style.display = "flex";

    document
      .querySelector(".accept-cookies-again")
      .addEventListener("click", () => {
        document.querySelector(".cookiesagainstpopup").style.display = "none";
        showAddedToCartModal(art, price, quantity);
      });
  }
}

function declineagain() {
  document.querySelector(".cookiesagainstpopup").style.display = "none";

  document.getElementById("fail").style.display = "flex";
}

function closeAddedToCartModal() {
  document.getElementById("addedToCartModal").style.display = "none";
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

function closeFailModal() {
  document.getElementById("fail").style.display = "none";
}

function navigateTo(art) {
  window.location.href = "https://fitokrama.by/art_page.php?art=" + art;
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeAddedToCartModal();
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key == "Escape") {
    closeAddedToCartModal();
  }
});
