
function gonorthSearch(){
  var cat=document.getElementById("gn-cat-select").value;
  var q=document.getElementById("gn-search-input").value.trim();
  var base="https://gonorth.co.il/places/";
  if(cat){base="https://gonorth.co.il/places/category/"+cat+"/";}
  if(q){base+="?s="+encodeURIComponent(q);}
  window.location.href=base;
}

document.getElementById("gn-cat-select").addEventListener("change",function(){
  if(this.value){window.location.href="https://gonorth.co.il/places/category/"+this.value+"/";}
  else{window.location.href="https://gonorth.co.il/places/";}
});

