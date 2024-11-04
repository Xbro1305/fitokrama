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
        const c = data.cart_count;
        console.log(document.querySelector(".cart_count"));
        console.log(document.querySelector(".cart_count").innerHTML);
        document.querySelector(".modalMobMsg").innerHTML = c;
        document.querySelector(".cart_count").innerHTML = c;
      })
      .catch(
        (error) => (document.getElementById("fail").style.display = "flex")
      )
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

document.addEventListener("DOMContentLoaded", () => {
  document.querySelector(".search-input").addEventListener("input", search);
});
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeAddedToCartModal();
  }
});

function search() {
  let searchValue = document.querySelector(".search-input").value;

  if (searchValue.length < 3) {
    document.querySelector(".search-results").style.display = "none";
    return;
  }

  fetch("https://fitokrama.by/search.php?search=" + searchValue)
    .then((response) => response.json())
    .then((data) => {
      let resultsContainer = document.querySelector(".search-results");
      resultsContainer.innerHTML = "";
      data.forEach((item) => {
        let resultItem = document.createElement("div");
        resultItem.className = "search-result-item";
        resultItem.textContent = item.name;
        resultItem.onclick = () => {
          window.location.href = item.art;
        };
        resultsContainer.appendChild(resultItem);
      });
      resultsContainer.style.display = "block";
    })
    .catch((error) => console.error(error));
}

document.addEventListener("keydown", (e) => {
  if (e.key == "Escape") {
    closeAddedToCartModal();
  }
});
