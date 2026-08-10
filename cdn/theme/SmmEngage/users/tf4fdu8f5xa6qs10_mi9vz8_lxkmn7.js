function toogleSidebar(){
   let togglebtn = document.getElementById('toggleSidebarBtn');
   let parentContainer = document.getElementById('parentContainer');
   parentContainer.classList.toggle('toggleSidebar');
}

function moreMenuToggle(){
   let moreTogglebtn = document.getElementById('moreMenu');
   let moreMenuContainer = document.getElementById('moreMenuContainer');

   moreMenuContainer.classList.toggle('actived');
}



function toggleThemeMode(){
   const bigoBdCurrentMode = localStorage.getItem('bigoBdCurrentMode');
   const bodyFire = document.getElementById('body');
   const toggleModes = document.getElementById('toggleModes');

   if(bigoBdCurrentMode){
           localStorage.removeItem('bigoBdCurrentMode');
           bodyFire.classList.remove('nightmode');
           toggleModes.classList.toggle('nightmodeActive');
   }else{
           localStorage.setItem('bigoBdCurrentMode', 'nightmode');
           bodyFire.classList.add('nightmode');
          
           toggleModes.classList.toggle('nightmodeActive');
   }
}
