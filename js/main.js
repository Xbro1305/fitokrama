function navigateTo(art) {
  window.location.href = `http://fitokrama.by/art_page.php?art=${art}`;
}

const getEmailCode = () => {
  const email = document.querySelector(".authWindowEmailInput").value;
  const emailST = email?.split("@");
  document.querySelector("#waiting").style.display = "flex";

  if (
    emailST[0]?.length > 1 &&
    emailST[1]?.split(".")[0]?.length > 1 &&
    emailST[1]?.split(".")[1]?.length > 1
  ) {
    fetch("https://fitokrama.by/confirm_email_request.php?email=" + email)
      .then((res) => res.json())
      .then((res) => {
        document.querySelector(".first-code-input").focus();
        document.querySelector(".getCodeAgainButton").innerHTML =
          '<i>Получить код повторно </i><i class="getCodeButtonZero">0:</i><i class="getCodeButtonTimer">59</i>';

        document.querySelector(".getCodeButton").style.display = "none";
        document.querySelector(".getCodeAgainButton").style.display = "block";

        document.querySelector(".emailCodeLabel").style.display = "block";

        let timer = 59;

        const interval = setInterval(() => {
          if (timer < 10) {
            document.querySelector(".getCodeButtonZero").innerHTML = "0:0";
          }
          document.querySelector(".getCodeButtonTimer").innerHTML = timer;
          timer--;
        }, 1000);

        setTimeout(() => {
          clearInterval(interval);

          document.querySelector(".getCodeAgainButton").innerHTML =
            "<i onclick='getEmailCode()' class='getCodeButton'>Получить код повторно</i>";
        }, 60000);
      })
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  } else {
    document.querySelector(".authWindowEmailInput").style.border =
      "2px solid red";
    return;
  }
};

function moveToNext(elem, index, inputs) {
  if (elem.value.length === 1) {
    if (index < inputs.length - 1) {
      inputs[index + 1].focus();
    } else {
      checkCode(inputs);
    }
  }
}

function checkBackspace(event, elem, index, inputs) {
  if (event.key === "Backspace" && elem.value === "") {
    if (index > 0) {
      inputs[index - 1].focus();
    }
  }
}

function checkCode(inputs) {
  let code = "";

  inputs.forEach((input) => {
    code += input.value;
  });

  if (code.length === 5) {
    const url = `https://fitokrama.by/check_authorization.php?code=${code}`;
    document.querySelector("#waiting").style.display = "flex";

    fetch(url)
      .then((res) => res.json())
      .then((res) => {
        if (res.status === "error") {
          document.querySelector("#universalConfirm").style.display = "flex";
          document.querySelector(".msg").innerHTML = res.message;
          inputs.forEach((input) => {
            input.value = "";
          });
          inputs[0].focus();
        } else {
          document.querySelector("#authModal").style.display = "none";
          document.querySelector("#confirmAuth").style.display = "flex";
          document.querySelector(".authMsg").innerHTML = res.message;
        }
      })
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  }
}

function moveToNextPhoneInput(elem, index, inputs) {
  if (elem.value.length === 1) {
    if (index < inputs.length - 1) {
      inputs[index + 1].focus();
    } else {
      checkCodePhoneInput(inputs);
    }
  }
}
function checkCodePhoneInput(inputs) {
  let code = "";

  inputs.forEach((input) => {
    code += input.value;
  });

  if (code.length === 5) {
    const url = `https://fitokrama.by/confirm_phone.php?code=${code}`;
    document.querySelector("#waiting").style.display = "flex";

    fetch(url)
      .then((res) => res.json())
      .then((res) => {
        if (res.status === "error") {
          document.querySelector("#universalConfirm").style.display = "flex";
          document.querySelector(".msg").innerHTML = res.message;
          inputs.forEach((input) => {
            input.value = "";
          });
          inputs[0].focus();
        } else {
          document.querySelector("#authModal").style.display = "none";
          document.querySelector("#confirmAuth").style.display = "flex";
          document.querySelector(".authMsg").innerHTML = res.message;
        }
      })
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  }
}

function checkBackspacePhoneInput(event, elem, index, inputs) {
  if (event.key === "Backspace" && elem.value === "") {
    if (index > 0) {
      inputs[index - 1].focus();
    }
  }
}

function exitaccount() {
  document.querySelector("#exitModal").style.display = "flex";
}

function confirmExitAccont() {
  document.querySelector("#waiting").style.display = "flex";

  fetch("https://fitokrama.by/unauthorization.php")
    .then((res) => res.json())
    .then((res) => {
      window.location.href = "https://fitokrama.by/catalog_page.php";
    })
    .finally(() => (document.querySelector("#waiting").style.display = "none"));
}

function showAuthMetods() {
  document.getElementById("authModal").style.display = "flex";
}

function checkPhoneAndEmail(response) {
  if (document.querySelector(".nonauthorized")) return;

  if (response.datetime_email_confirmed == null) {
    document.querySelector(".EC").style.display = "none";
    document.querySelector(".ENC").style.display = "flex";
  } else if (response.datetime_email_confirmed != null) {
    document.querySelector(".ENC").style.display = "none";
    document.querySelector(".EC").style.display = "flex";
  }

  if (response.datetime_phone_confirmed == null) {
    document.querySelector(".PC").style.display = "none";
    document.querySelector(".PNC").style.display = "flex";
  } else if (response.datetime_phone_confirmed != null) {
    document.querySelector(".PNC").style.display = "none";
    document.querySelector(".PC").style.display = "flex";
  }

  if (document.querySelector("#client_email").value == "") {
    document.querySelector(".ENC").style.backgroundColor = "#ccc";
    document.querySelector(".ENC").disabled = true;
  } else if (document.querySelector("#client_email").value != "") {
    document.querySelector(".ENC").style.backgroundColor = "rgb(97, 129, 98);";
    document.querySelector(".ENC").disabled = false;
  }

  if (document.querySelector("#client_phone").value == "") {
    document.querySelector(".PNC").style.backgroundColor = "#ccc";
    document.querySelector(".PNC").disabled = true;
  } else if (document.querySelector("#client_phone").value != "") {
    document.querySelector(".PNC").style.backgroundColor = "rgb(97, 129, 98);";
    document.querySelector(".PNC").disabled = false;
  }
}

const openConfirmEmail = () => {
  const emailtimerzero = document.querySelector(".emailtimerzero");

  emailtimerzero.innerHTML = "0:";

  let timer = 59;

  const interval = setInterval(() => {
    if (timer < 10) {
      document.querySelector(".emailtimerzero").innerHTML = "0:0";
    }

    document.querySelector(".emailtimer").innerHTML = timer;
    document.querySelector("#waiting").style.display = "flex";

    timer--;
  }, 1000);
  document.getElementById("confirmEmail").style.display = "flex";

  fetch("https://fitokrama.by/confirm_email_request.php").finally(
    () => (document.querySelector("#waiting").style.display = "none")
  );

  document.querySelector(".first-email-code-input").focus();

  setTimeout(() => {
    const sendbutton = document.querySelector(".sendCodeAgainEmail");
    document.querySelector(".emailtimerzero").style.display = "none";
    document.querySelector(".emailtimer").style.display = "none";
    sendbutton.classList.add("available");
    sendbutton.classList.add("availableEmail");
    clearInterval(interval);

    document.querySelector(".availableEmail").addEventListener("click", () => {
      sendbutton.classList.remove("available");
      sendbutton.classList.remove("availableEmaill");
      const emailtimer = document.querySelector(".emailtimer");

      emailtimerzero.style.display = "flex";
      emailtimerzero.innerHTML = "0:";

      emailtimer.style.display = "flex";
      emailtimer.innerHTML = 59;

      clearInterval(interval);
      openConfirmEmail();
    });
  }, 60000);
};

function closeAuthModal() {
  document.querySelector("#confirmAuth").style.display = "none";
  window.location.reload();
}

document.querySelectorAll(".code-input").forEach((input) => {
  input.addEventListener("paste", (e) => {
    const pasteData = e.clipboardData.getData("text");

    if (/^\d{5}$/.test(pasteData)) {
      const inputs = document.querySelectorAll(".code-input");

      inputs.forEach((input, i) => (input.value = pasteData[i]));

      checkCode(inputs);
    }

    e.preventDefault();
  });

  input.addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, "");
  });
});

document.querySelectorAll(".email-code-input").forEach((input) => {
  input.addEventListener("paste", (e) => {
    const pasteData = e.clipboardData.getData("text");

    if (/^\d{5}$/.test(pasteData)) {
      const inputs = document.querySelectorAll(".email-code-input");

      inputs.forEach((input, i) => (input.value = pasteData[i]));

      checkCode(inputs);
    }

    e.preventDefault();
  });

  input.addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, "");
  });
});

document.querySelectorAll(".phone-code-input").forEach((input) => {
  input.addEventListener("paste", (e) => {
    const pasteData = e.clipboardData.getData("text");

    if (/^\d{5}$/.test(pasteData)) {
      const inputs = document.querySelectorAll(".phone-code-input");

      inputs.forEach((input, i) => (input.value = pasteData[i]));

      checkCodePhoneInput(inputs);
    }

    e.preventDefault();
  });

  input.addEventListener("input", (e) => {
    e.target.value = e.target.value.replace(/[^0-9]/g, "");
  });
});

document.querySelectorAll(".sendinginfo").forEach((i) => {
  i.addEventListener("blur", (e) => {
    document.querySelector("#waiting").style.display = "flex";

    fetch("https://fitokrama.by/cart_edit.php", { method: "POST" })
      .then((response) => response.json())
      .then((res) => {
        if (
          res?.client_email_nochange_text &&
          e.target.id == "client_email" &&
          e.target.value != res.client_email
        ) {
          document.querySelector("#universalConfirm").style.display = "flex";
          document.querySelector(".msg").innerHTML =
            res?.client_email_nochange_text;
          document.getElementById(e.target.id).value = res[e.target.id];
        } else if (
          res?.client_phone_nochange_text &&
          e.target.id == "client_phone" &&
          e.target.value != res.client_email
        ) {
          document.querySelector("#universalConfirm").style.display = "flex";
          document.querySelector(".msg").innerHTML =
            res.client_phone_nochange_text;
          document.getElementById(e.target.id).value = res[e.target.id];
        } else {
          fetch("https://fitokrama.by/cart_edit.php", {
            method: "POST",
            body: JSON.stringify({
              [e.target.id]: e.target.value,
            }),
          })
            .then((response) => response.json())
            .then((res) => {
              checkPhoneAndEmail(res);
              document.querySelector(".totalPriceRub").innerHTML =
                res.delivery_price_rub;
              document.querySelector(".totalPriceKop").innerHTML =
                res.delivery_price_kop;
            })
            .catch((error) => console.error(error));
        }
      })
      .catch((err) => console.log(err))
      .finally(
        () => (document.querySelector("#waiting").style.display = "none")
      );
  });
});

window.navigateTo = navigateTo;
window.getEmailCode = getEmailCode;
window.moveToNext = moveToNext;
window.checkBackspace = checkBackspace;
window.checkCode = checkCode;
window.moveToNextPhoneInput = moveToNextPhoneInput;
window.checkBackspacePhoneInput = checkBackspacePhoneInput;
window.checkCodePhoneInput = checkCodePhoneInput;
// window.showAccountActions = showAccountActions;
window.exitaccount = exitaccount;
window.showAuthMetods = showAuthMetods;
window.checkPhoneAndEmail = checkPhoneAndEmail;
window.openConfirmEmail = openConfirmEmail;
window.closeAuthModal = closeAuthModal;
