window.waitForElementToExist = function (selector) {
  return new Promise(resolve => {
    if (document.querySelector(selector)) {
      return resolve(document.querySelector(selector));
    }

    const observer = new MutationObserver(() => {
      if (document.querySelector(selector)) {
        resolve(document.querySelector(selector));
        observer.disconnect();
      }
    });

    observer.observe(document.documentElement || document.body, {
      subtree: true,
      childList: true,
    });
  });
}
window.insertSvgLoading = function (element) {
  let svg = `
      <div class="loader-container">
        <div class="loader-frame">
            <svg id="Layer_2" class="logo-loading" width="60" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 525.71 642.83">
            <defs>
                <style>
                    .cls-1 {
                        stroke-width: 0px;
                    }

                    .cls-2 {
                        stroke: #000;
                        stroke-miterlimit: 10;
                        stroke-width: 9px;
                    }
                </style>
            </defs>
            <g id="Layer_1-2" data-name="Layer 1">
                <g>
                    <path class="cls-2"
                        d="m467.3,206.33l6.22-198.22-150.22,89.78h-121.78L50.42,8.1l8,198.22L5.08,349.44l219.56,288.89h78.22l217.78-288-53.33-144Zm-159.23,413.11h-87.11l-72-161.78-126.22-114.67,62.22-123.56,21.33,5.33-27.56-172.44,123.56,75.56,62.22-12.44,64.89,11.56,124.44-74.67-29.33,176,21.33-7.11,60.44,121.78-123.56,116.44-74.67,160Z" />
                    <polygon class="cls-1" points="110.6 109.22 127.78 214.7 171.04 148.92 110.6 109.22" />
                    <polygon class="cls-1" points="414.69 108.63 397.99 214.19 354.43 148.61 414.69 108.63" />

                    <polygon class="cls-1 eye"
                        points="117.12 334.1 148.52 387.14 204.23 393.66 227.04 411.73 210.75 378.84 117.12 334.1" />
                    <polygon class="cls-1 eye"
                        points="409.08 334.1 377.67 387.14 321.97 393.66 299.16 411.73 315.45 378.84 409.08 334.1" />
                    <polygon class="cls-1"
                        points="224.08 524.4 231.42 554.66 262.86 579.55 295.13 553.24 302.25 524.4 264.52 534.16 224.08 524.4" />
                </g>
            </g>
            </svg>
        </div>
        </div>
            `;


  element.insertAdjacentHTML('beforeend', svg);
  let params = new URLSearchParams(window.location.search);
  if (params.get('theme') === 'dark') {

    let svgElements = document.querySelectorAll('.cls-1, .cls-2');
    svgElements.forEach(function (element) {
      element.style.fill = 'white';
    });
  }
}
// 👇️ using the function
waitForElementToExist('.dataTables_processing').then(element => {
  insertSvgLoading(element)
});
waitForElementToExist('body #app .page').then(element => {
  insertSvgLoading(element)
});


window.addEventListener('load', (event) => {
  const loaderFrame = document.querySelector('body #app .page>.loader-container');
  if (loaderFrame) {
    loaderFrame.remove();
  }
});


$(document).on('keydown', 'form',function(e){
  if (e.key === 'Enter') {
      if ($(e.target).closest('.ck.ck-content').length === 0) {
        e.preventDefault();
    }
  }
})

$(document).ajaxSend(function (e, xhr, settings) {
  let activeElement = e.currentTarget.activeElement;
  let activeContainer
  if (activeElement.tagName === 'BUTTON') {
    let closestForm = $(activeElement).closest('form');
    let modal = $(activeElement).closest('.modal-dialog'); 
    if(modal.length){
      activeContainer = modal
      insertSvgLoading(modal[0])
    }
    else{
      if (closestForm.length) {
        activeContainer = closestForm
        insertSvgLoading(closestForm[0])
      }
    }
    activeElement.disabled = true;

  } 
  xhr.always(function () {
    activeContainer
    if(activeContainer){
      const loaderFrame = $(activeContainer).find('.loader-container')
      if (loaderFrame) {
        loaderFrame.remove();
      }
    }
    activeElement.disabled = false;

  });
});


let ajaxInProgress
$(document).ajaxStart(function() {
  ajaxInProgress = true;
}).ajaxStop(function() {

  ajaxInProgress = false;
});

window.addEventListener("beforeunload", function (e) {
  if (ajaxInProgress) {
      var confirmationMessage = 'Một yêu cầu đang diễn ra, bạn có chắc chắn muốn rời khỏi?';
      (e || window.event).returnValue = confirmationMessage;
      return confirmationMessage;
  }
});
