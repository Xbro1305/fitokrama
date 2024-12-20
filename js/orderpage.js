function handleQRClick(modalId, paylink) {
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    if (isMobile) {
        // На мобильных устройствах сразу переходим по ссылке
        window.open(paylink, '_blank');
    } else {
        // На десктопе открываем модальное окно
        openModal(modalId);
    }
}

function openModal(modalId) {
  document.getElementById(modalId).style.display = "flex";
}

function openTextModal(message) {
  document.getElementById("modalMessageContent").textContent = message;
  openModal("messageModal");
}
function openCustomWindow(url) {
  window.open(
    url,
    "_blank",
    "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600"
  );
}

window.openModal = openModal;
window.openTextModal = openTextModal;
window.openCustomWindow = openCustomWindow;
