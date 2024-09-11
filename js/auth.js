function showAccountActions() {
  window.location.href = "https://fitokrama.by/profile_page.php";
}

function handleAppleCredentialResponse(response) {
  document.querySelector("#waiting").style.display = "flex";

  fetch("check_authorization.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify({
      id_token: response.id_token,
      code: response.code,
      state: response.state,
    }),
  })
    .then((response) => response.json())

    .then((result) => {
      document.querySelector("#authModal").style.display = "none";

      if (result.status === "ok" && result.JWT) {
        setCookie("jwt", result.JWT, 365);

        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;
      } else {
        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;
      }
    })

    .catch((error) => {
      document.querySelector("#confirmAuth").style.display = "flex";

      document.querySelector(".authMsg").innerHTML = error.message;
    })
    .finally(() => (document.querySelector("#waiting").style.display = "none"));
}

document.addEventListener("DOMContentLoaded", function () {
  AppleID?.auth?.init({
    clientId: "com.fitokramaby.app",

    scope: "name email",

    redirectURI: "https://fitokrama.by/check_authorization.php",

    state: "initial_state",

    usePopup: true,

    responseMode: "fragment",
  });

  document
    .querySelector(".authWithAppleID")
    .addEventListener("click", function () {
      AppleID.auth
        .signIn()
        .then(function (response) {
          if (response.authorization && response.authorization.id_token) {
            handleAppleCredentialResponse(response.authorization);
          } else {
            document.querySelector("#universalConfirm").style.display = "flex";

            document.querySelector(".msg").innerHTML = "Что-то пошло не так";
          }
        })
        .catch(function (error) {});
    });
});
window.onload = function () {
  google.accounts.id.initialize({
    client_id:
      "623477736615-btbfr5k08fbrkkusiprte24ip5vse4rb.apps.googleusercontent.com",

    callback: handleCredentialResponse,
  });
};

function onGoogleSignIn() {
  google.accounts.id.prompt(); // Открытие окна авторизации Google
}
const checkAuthEmail = () => {
  const email = document.querySelector(".authWindowEmailInput").value;
  const emailST = email?.split("@");

  if (
    emailST[0]?.length > 1 &&
    emailST[1]?.split(".")[0]?.length > 1 &&
    emailST[1]?.split(".")[1]?.length > 1
  ) {
    document.querySelector(".authWindowEmailInput").style.border =
      "1px solid #ccc";
    document.querySelector(".getButton").style.display = "flex";
  } else {
    document.querySelector(".authWindowEmailInput").style.border =
      "2px solid red";
    document.querySelector(".getButton").style.display = "none";
  }
};

function getCookie(name) {
  let matches = document.cookie.match(
    new RegExp(
      "(?:^|; )" + name.replace(/([.$?*|{}()[]\/+^])/g, "\\$1") + "=([^;]*)"
    )
  );

  return matches ? decodeURIComponent(matches[1]) : undefined;
}

function setCookie(name, value, options = {}) {
  options = {
    path: "/",
    ...options,
  };

  if (options.expires instanceof Date) {
    options.expires = options.expires.toUTCString();
  }

  let updatedCookie =
    encodeURIComponent(name) + "=" + encodeURIComponent(value);

  for (let optionKey in options) {
    updatedCookie += "; " + optionKey;

    let optionValue = options[optionKey];

    if (optionValue !== true) {
      updatedCookie += "=" + optionValue;
    }
  }

  document.cookie = updatedCookie;
}

let sessionId = getCookie("session_id");

let userId = getCookie("user_id");
(() => {
  if (!sessionId) {
    document.querySelector(".cookiespopup").style.display = "flex";
  }
})();

function accept() {
  if (!sessionId) {
    sessionId = [...Array(32)]
      .map(() => Math.floor(Math.random() * 16).toString(16))
      .join("");

    setCookie("session_id", sessionId, { "max-age": 30 * 24 * 60 * 60 });
  }

  document.querySelector(".cookiespopup").style.display = "none";
}

function decline() {
  document.querySelector(".cookiespopup").style.display = "none";
}

function setCookie(name, value, days) {
  var expires = "";

  if (days) {
    var date = new Date();

    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);

    expires = "; expires=" + date.toUTCString();
  }

  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function handleCredentialResponse(response) {
  document.querySelector("#waiting").style.display = "flex";
  fetch("check_authorization.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify({ credential: response.credential }),
  })
    .then((response) => response.json())

    .then((result) => {
      document.querySelector("#authModal").style.display = "none";

      if (result.status === "ok" && result.JWT) {
        setCookie("jwt", result.JWT, 365);

        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;
      } else {
        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;
      }
    })

    .catch((error) => {
      document.querySelector("#confirmAuth").style.display = "flex";

      document.querySelector(".authMsg").innerHTML = error.message;
    })
    .finally(() => (document.querySelector("#waiting").style.display = "none"));
}

function onTelegramAuth(user) {
  const url = new URL("check_authorization.php", window.location.href);

  url.searchParams.append("id", user.id);

  url.searchParams.append("first_name", user.first_name);

  url.searchParams.append("username", user.username);

  url.searchParams.append("photo_url", user.photo_url);

  url.searchParams.append("auth_date", user.auth_date);

  url.searchParams.append("hash", user.hash);

  fetch(url)
    .then((response) => response.json())

    .then((result) => {
      if (result.status === "ok" && result.JWT) {
        setCookie("jwt", result.JWT, 365);

        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;

        document.querySelector("#authModal").style.display = "none";
      } else {
        document.querySelector("#confirmAuth").style.display = "flex";

        document.querySelector(".authMsg").innerHTML = result.message;

        document.querySelector("#authModal").style.display = "none";
      }
    })
    .catch((error) => {
      document.querySelector("#confirmAuth").style.display = "flex";

      document.querySelector(".authMsg").innerHTML = error.message;
      document.querySelector("#authModal").style.display = "none";
    });
}

window.showAccountActions = showAccountActions;
window.handleAppleCredentialResponse = handleAppleCredentialResponse;
window.onGoogleSignIn = onGoogleSignIn;
window.accept = accept;
window.decline = decline;
window.setCookie = setCookie;
window.handleCredentialResponse = handleCredentialResponse;
window.getCookie = getCookie;
window.onTelegramAuth = onTelegramAuth;
window.checkAuthEmail = checkAuthEmail;
