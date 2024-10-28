const img = document.querySelector(".bannerWrapper");
const pagination = document.querySelector(".pagination");

const imagesArry = [
  '<div class="b b2"><p style="color: #fff !important">здоровая экономия</p><h1 style="color: #fff !important">СРАВНИ ЦЕНЫ!</h1></div>',
  '<div class="b"><h1>ТОП-10</h1><p>для суставов</p></div>',
];

let currentImage = 0;

pagination.innerHTML = imagesArry
  .map(
    (i, index) =>
      `<button onclick="changeCurrentimage(${index})" class="dot"></button>`
  )
  .join("");

const dots = document.querySelectorAll(".dot");

dots[currentImage].classList.add("active");
img.innerHTML = imagesArry[currentImage];

function changeImage() {
  img.innerHTML = imagesArry[currentImage];
  dots.forEach((dot) => dot.classList.remove("active"));
  dots[currentImage].classList.add("active");
}

async function moveTo(num) {
  if (num == -1 && currentImage === 0) {
    currentImage = imagesArry.length - 1;
  } else if (num == 1 && currentImage === imagesArry.length - 1) {
    currentImage = 0;
  } else {
    currentImage += num;
  }

  changeImage();
}

const changeCurrentimage = (index) => {
  currentImage = index;
  changeImage();
};

setInterval(() => moveTo(1), 5000);
