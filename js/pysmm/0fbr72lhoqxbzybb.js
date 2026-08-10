/**
 * SNFollows Theme
 * Author: SÃ¼leyman Kandilci
 * Designer: Bekir Can AydÄ±n
 * Mail: duzst97@gmail.com / mail@smmspot.net / hello@suleymankndlc.me
 */
const changeCatBtns = document.querySelectorAll('.nwoCtFlt');

if (changeCatBtns[0]) {
  const orderFormCats = document.getElementById('neworder_category');
  var realData = orderFormCats.innerHTML;

  const categories = [];

  [...changeCatBtns].forEach(btn => {
    categories.push(btn.dataset.changeCat);
    btn.addEventListener('click', e => {
      const val = btn.getAttribute('data-change-cat');
      const orderFormCats = document.getElementById('orderform-category');
      const options = document.querySelectorAll('#orderform-category-copy option');

      [...changeCatBtns].forEach(bt => {
        bt.classList.remove('active');
      });
      btn.classList.add('active');

      const newOptions = [];
      [...options].forEach(el => {
        if (val == 'other') {
          // if el inner text is not in categories
          let isIn = false;
          [...categories].forEach(cat => {
            if (cat.toLowerCase() == 'other' || cat == null || cat == "" || cat.trim() == '' || cat.trim == null) {

            } else {
              if (el.innerText.toLowerCase().includes(cat.toLowerCase())) {
                isIn = true;
              }
            }
          });
          if (!isIn) {
            newOptions.push(el);
          }
        } else {
          if (el.innerText.toLowerCase().includes(val.toLowerCase())) {
            newOptions.push(el);
          }
        }
      });
      const newOptionsHtml = [];
      [...newOptions].forEach(el => {
        newOptionsHtml.push(el.outerHTML);
      });
      orderFormCats.innerHTML = newOptionsHtml.join('');

      $('#orderform-category').trigger('change');
    });
  });

  /**
     * copy order form data hidden
     */
  setTimeout(() => {
    const orderFormCopy = document.createElement('select');
    orderFormCopy.setAttribute('id', 'orderform-category-copy');
    orderFormCopy.style.display = 'none';
    orderFormCopy.innerHTML = realData;
    orderFormCats.parentNode.insertBefore(orderFormCopy, orderFormCats);
  }, 1);

  const nocWrapper = document.getElementById('noc-wrapper');
  nocWrapper.style.display = 'none';
  
  setTimeout(() => {
    nocWrapper.style.display = 'block';
  }, 1);
}
//* end */


/**
 * useState
 */

const useState = (defaultValue) => {
  let value = defaultValue;
  const getValue = () => value
  const setValue = newValue => value = newValue
  return [getValue, setValue];
}

/**
 * header scroll fixed
 */

const header = document.querySelector('#noAuthHeader');
if (header) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 30) {
      header.classList.add('hActive');
    } else {
      header.classList.remove('hActive');
    }
  });
}

var swiperOptions = {
  loop: true,
  freeMode: true,
  spaceBetween: 30,
  grabCursor: true,
  loop: true,
  autoplay: {
    delay: 1,
    disableOnInteraction: false,
    pauseOnMouseEnter: true
  },
  freeMode: true,
  speed: 8000,
  freeModeMomentum: false,
  breakpoints: {
    0: {
      slidesPerView: 1.5,
    },
    998: {
      slidesPerView: 2,
    }
  }
};

var swiper = new Swiper("#noaTes", swiperOptions);
swiperOptions.speed = 6000;
var swiper = new Swiper("#noaTes2", swiperOptions);

/**
 * dashboard
 */

/**
 * sidebar show more
 */

const showMoreItem = document.querySelector('.mSMoreItem');
if (showMoreItem) {

  const activeControl = showMoreItem.querySelectorAll('.mLink.active');
  if (activeControl.length > 0) {
    showMoreItem.classList.add('active');
  } else {
  }

  showMoreItem.addEventListener('click', () => {
    const subMenu = showMoreItem.querySelector('.subMenu');
    if (!showMoreItem.classList.contains('active')) {
      subMenu.style.transition = '0s';
      subMenu.style.opacity = 0;
      subMenu.style.transform = 'translateY(30px)';
      showMoreItem.classList.toggle('active');
      subMenu.style.transition = ".2s ease";
      setTimeout(() => {
        subMenu.style.opacity = 1;
        subMenu.style.transform = 'translateY(0)';
      }, 1);
    } else {
      subMenu.style.transition = '0s';
      subMenu.style.opacity = 1;
      subMenu.style.transform = 'translateY(0)';
      subMenu.style.transition = ".2s ease";
      setTimeout(() => {
        subMenu.style.opacity = 0;
        subMenu.style.transform = 'translateY(30px)';
      }, 1);
      setTimeout(e => {
        showMoreItem.classList.toggle('active');
      }, 205)
    }
  });
}

const bodyDashboard = document.getElementById('appDashboard');
if (bodyDashboard) {
  // write title to header
  const headerTitle = document.querySelector('.apPgTi');
  const headerText = document.querySelector('.apPgTx');
  const titleDOM = document.getElementById('appTt');
  const textDom = document.getElementById('appTx');
  if (titleDOM && headerTitle) {
    headerTitle.innerHTML = titleDOM.innerHTML;
  }
  if (textDom && headerText) {
    headerText.innerHTML = textDom.innerHTML;
  }
}

// accordion 
// get all .aq elements
const aq = document.querySelectorAll('.aq');
// foreach all aq elements
[...aq].forEach(el => {
  // find element children 
  const children = el.querySelectorAll('.aq-item');
  // foreach children elements
  [...children].forEach(child => {
    // toggle class active when clicked
    child.children[0].addEventListener('click', function () {
      // remove active class from all children
      const isActive = child.classList.contains('active');
      [...children].forEach(child => {
        child.classList.remove('active');
      });
      // add active class to clicked child
      if (!isActive) {
        child.classList.toggle('active');
      }
    });
  });
});

/**
 * chat go bottom
 */

var sChatBody = document.getElementsByClassName('schat-chat-body')[0];
if (sChatBody) {
  sChatBody.scrollTo(0, sChatBody.offsetHeight);
}

/**
 * gender switch
 */

const [gender, setGender] = useState('male');
const genderLocal = localStorage.getItem('gender');
// if gender is undefined
if (genderLocal !== null) {
  setGender(genderLocal);
}

const genderChangeAvatar = () => {
  const getAllAvatars = document.querySelectorAll(`[alt="Avatar"]`);
  const avatarUrl = gender() == 'female' ? 'https://cdn.smmspot.net/snfollows/assets/img/avatar_woman.png' : 'https://cdn.smmspot.net/snfollows/assets/img/avatar.png';
  console.log(gender(), avatarUrl);
  if (getAllAvatars[0]) {
    [...getAllAvatars].forEach(img => {
      img.setAttribute('src', avatarUrl);
    });
  }
}

// document ready
document.addEventListener('DOMContentLoaded', () => {
  genderChangeAvatar();
});

const genderSwitch = document.getElementById('gender-switch');
if (genderSwitch) {
  if (gender() == 'male') {
    genderSwitch.classList.add('gs-male');
  } else {
    genderSwitch.classList.add('gs-female')
  }
  genderSwitch.addEventListener('click', e => {
    if (gender() == 'male') {
      genderSwitch.classList.remove('gs-male');
      genderSwitch.classList.add('gs-female');
      setGender('female');
    } else {
      genderSwitch.classList.remove('gs-female');
      genderSwitch.classList.add('gs-male');
      setGender('male');
    }

    localStorage.setItem('gender', gender());
    genderChangeAvatar();
  });
}

/**
 * noathmenutoggle
 */

var noAuthMenuToggle = () => {
  header.classList.toggle('active');
}

/**
 * appAuth
 */

const appAuth = document.querySelector('.appAuth');

var authMenuToggle = e => {
  appAuth.classList.toggle('sidebar-active');
}

/**
 * modalOpen
 */

var modalOpen = (modalId, data = null) => {
  const modal = document.getElementById(modalId);
  const modalBox = modal.querySelector('.modal-box');
  modal.classList.add('active');
  // add to body stop scroll
  document.body.style.overflow = 'hidden';

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
  }

  // close modal when click outside modalBox
  modal.addEventListener('click', e => {
    // if target is not modalBox or modalBox children
    if (e.target !== modalBox && !modalBox.contains(e.target)) {
      closeModal();
    }
  });

  const modalCloseBtn = modal.querySelector('.m-close');
  if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', e => {
      closeModal();
    })
  }

  if (data != null) {
    Object.keys(data).forEach(key => {
      const el = document.getElementById(key);
      if (el) {
        el.innerHTML = data[key];
      }
    });
  }

}
$("#orderform-service").change(function () {
  service_id = $(this).val();
  $("#s_id").text(service_id);

  description = window.modules.siteOrder.services[service_id].description
  $("#s_desc").html(description);

  name = window.modules.siteOrder.services[service_id].name
  $("#s_name").html(name);

  console.log($("#s_time").text());
  service_time_text = window.modules.siteOrder.services[service_id].average_time
  $("#s_time").text(service_time_text);
  $('#s_time').val($('#s_time').text());

})

