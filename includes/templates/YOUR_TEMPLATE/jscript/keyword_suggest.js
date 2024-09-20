/**
 * Product Suggest
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 1.0
 * @Release Date: Monday, May 10 2018 PST
 * @Tested on Zen Cart v1.5.5 $
 */
var its;
var textField;
var lastKeyPress;
var sl = -1; //selected listing (default)

const boxID = "suggestbx";
const numItems = 10;
const minKeywordLength = 3;
const descMaxLen = 200;
const posHorizontalOffset = 0;
const posVerticalOffset = 25;

window.onload = function(){
	if (typeof jQuery == 'undefined') { //if jquery not loaded
		var script = document.createElement('script');
		script.src = '//code.jquery.com/jquery-3.3.1.min.js';
		script.type = 'text/javascript';
		document.getElementsByTagName('head')[0].appendChild(script);
	}
}

function ProductSuggest(el){
	if(el!==undefined){
		textField = el;
	}
	$('[name="'+textField.name+'"]').attr("autocomplete", "off");
	var nav_keys = [13,38,40,27,8];
	if( nav_keys.includes(lastKeyPress) ){
		if( lastKeyPress == 13 ){
			EnterKeyPress();
		}
		return; //exit
	}
	
	if( textField.value.length >= minKeywordLength ){
		var ajx = $.ajax({
			url: "ajax.php",
			type: "GET",
			data: {
					act:	"ProductSuggest",
					method:	"SuggestProduct",
					keyword:textField.value,
					limit:	numItems,
					descLen:descMaxLen
			}
		});
		
		ajx
		.done( function( items ){
			var itemsList = JSON.parse( JSON.parse( items ) );
			if( typeof itemsList =='object' && itemsList.length > 0 ){
				if( document.getElementById(boxID) === null ){
					CreateSuggestBox(boxID);
				}
				ClearSuggestions(boxID);
				LoadSuggestions(itemsList, boxID);
				SuggestBoxPosition(textField, boxID);
			}
		})
		.fail(function(xhr) {
			console.log('Error: ', xhr);
		});
	}else{
		ClickClose();
	}
}

function ClearSuggestions( id ){
	var n = document.getElementById( id );
	while ( n.firstChild ) {
		n.removeChild( n.firstChild );
	}
}

function LoadSuggestions(items, boxID){
	var i = 0;
	its = [];
	$.each(items, function(index, value){
		var listing = AddSuggestBoxItem( i, value['id'], value['image'], value['name'], CleanString(value['descr']) );
		document.getElementById(boxID).appendChild(listing);
		its.push({'id': value['id'], 'name': value['name'], 'image': value['image'], 'descr': CleanString(value['descr'])});
		i++;
	});
	var elClose = document.createElement("div");
	elClose.setAttribute("id", "cs");
	elClose.setAttribute("onClick","javascript:ClickClose();");
	elClose.textContent = "close";
	document.getElementById(boxID).appendChild( elClose );
	$("#" + boxID).css("display","block");
}

function CreateSuggestBox(id){
	var el = document.createElement("div");
	el.setAttribute("id", id);
	document.body.appendChild(el);
}

function AddSuggestBoxItem(index,id,image,name,description){
	var el = document.createElement("div");
	el.setAttribute( "id", "h" + index);
	el.setAttribute( "class", "pline");
	el.setAttribute( "onmouseover", "javascript:SelectItem(" + index + ");");
	el.setAttribute( "onClick", "javascript:ClickItem(" + id + ");");
	
	var img = document.createElement("img");
	img.setAttribute("src", "images/" + image);
	
	var eImg = document.createElement("div");
	eImg.setAttribute("class", "pimg");
	eImg.appendChild(img);
	
	var eName = document.createElement("div");
	eName.setAttribute("class","pname");
	eName.textContent = name;
	
	var eDesc = document.createElement("div");
	eDesc.setAttribute("class", "pdesc");
	eDesc.textContent = description;
	
	el.appendChild(eImg);
	el.appendChild(eName);
	el.appendChild(eDesc);
	
	return el;
}

function CleanString( str ){
	if( str != undefined ){
		str = str.replace(/[^\w\s]/gi, ' ');
		str = str.replace(/\s\s+/g, ' ');
	}
	return str;
}

function SuggestBoxPosition(refEl, bxID){
	if( its !== null && its.length > 0){
		var top  = Math.round( $('[name="'+refEl.name+'"]').offset().top ) + posVerticalOffset;
		var left = Math.round( $('[name="'+refEl.name+'"]').offset().left ) + posHorizontalOffset;
		$("#" + bxID).css("top", top + "px");
		$("#" + bxID).css("left",left + "px");
	}
}

function ClickItem(id){
	var pathArray = location.href.split( '/' );
	var protocol = pathArray[0];
	var host = pathArray[2];
	if( id > 0 ){
		window.location = protocol + '//' + host + '/index.php?main_page=product_info&products_id=' + id;
	}else{
		window.location = protocol + '//' + host + '/index.php?main_page=quote_request';
	}
}

function ClickClose(){
	$("#" + boxID).html("");
	$("#" + boxID).css("display","none");
	sl = -1;
	its = [];
}

function ClearSelect(){
	if (typeof its !== "undefined"){
		for( i = 0; i < its.length; i++){
			$("#h" + i).removeClass("pline_on");
			$("#h" + i).addClass("pline");
		}
	}else{
		ClickClose();
	}
}

document.addEventListener("keydown", function( e ) {
	if (typeof its === "undefined"){
		sl = -1;
	}else{
		if(e){
			lastKeyPress = e.which;
		}else{
			lastKeyPress = -1;
		}
		
		switch(lastKeyPress){
			case 13:
				EnterKeyPress();
				break;
			case 38:
				ArrowUpPress();
				break;
			case 40:
				ArrowDownPress();
				break;
			case 27:
				EscKeyPress();
				break;
			case 8:
				BackspacePress();
				break;
			default:
				break;
		}
	}
});

function SelectItem(inum){
	ClearSelect();
	$("#h" + inum).addClass("pline_on");
}

function EnterKeyPress(){
	if( sl >= 0 ){
		ClickItem(its[sl]['id']);
	}
}

function ArrowUpPress(){
	if( sl > 0 ){
		sl -= 1;
		SelectItem(sl);
	}else{
		sl = -1;
		ClearSelect();
	}
}

function ArrowDownPress(){
	if( sl < (its.length-1) ){
		sl += 1;
		SelectItem(sl);
	}
}

function EscKeyPress(){
	ClickClose();
}

function BackspacePress(){
	sl = -1;
	if( textField !== undefined && textField.value.length > 3 ){
		ClearSelect();
	}else{
		ClickClose();
	}
}