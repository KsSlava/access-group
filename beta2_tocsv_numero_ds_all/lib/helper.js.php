<script type="text/javascript">
var ids      = [];  var k = 0;
var accMax   ='';   var a = 0;
var upd      = 0;   var noupd =0;
var r        = 0;  //r1, r2  #id of result
var source   = '';

var endGrabb            = 0; //0 or 1
var endGrabbAllAccounts = 0; // 0 or 1

var endGrabbNum         = 0; //counter: 0 ++
var endGrabbTaskNum     = 0; //counter: 0 ++
var endUpdateTaskNum    = 0; //counter: 0 ++

var u; //user
var uID = Math.floor(Math.random() * 10000) + 100;

var taskGrabb = 0; 
var taskUpdate = 0; 

var updateLinks = ["vp/index.php","v/index.php"  ];

var nI = 0; 
var nextIds = []; 


function createNextIds(){

	ids = []; 

	for(i=0; i<=nextIds.length-1; i++){

		ids[i] = nextIds[i]; 

	}


    if(ids.length<1) { update()
	}
	nextIds = []; nI = 0; 

}


function update(){

	$.ajax({

		url:updateLinks[endUpdateTaskNum],
		method:'POST',
		async: false,
		data: {do:'update'},
		dataType:'text',
		error: function(){

			console.log('error');
		},
		success: function(e){

			u = e;

            dsp = $('#r2').html();
            $('#r2').html(dsp+' updated:'+u);

			endUpdateTaskNum++;
			
            if(endUpdateTaskNum==1){update()} 
		}
			
			

	});

}









function dispayAddContainer(){
	//add container for account 
    	
    	$('#r'+source).append('<div id="r'+source+'_'+a+'"></div>'); 

}

function displayResults($addresult){

    	if(addresult=="ok") {upd++}
        if(addresult=="no") {noupd++}

        if(k==0) {

              dispayAddContainer();
        }

        str = (k+1)+'/'+ids.length + '&nbsp;&nbsp;upd:&nbsp;'+ upd + '&nbsp;&nbsp;no:&nbsp; '+noupd + '&nbsp;&nbsp;id:&nbsp; '+id; 
        
        // source + num of account
        $('#r'+source+'_'+a).html(str);
}

function getList(source){

	$.ajax({

		url:'vpindex.php',
		method:'POST',
		async: false,
		data: {do:"getList", source:source},
		dataType:'json',
		success: function(e){

			ids   = e.ids;
			accMax = e.accMax -1;  
             
		}

	});

}



function grabb(){
	console.log(ids.length);
	if(ids.length>0){

		endGrabb = 0; 

		id = ids[k];

	   	$.ajax({

			url:'vpindex.php',
			method:'POST',
			async: false,
			data: {do:"grabb", id:id, k:k, source:source, a:a},
			dataType:'json text',
			 error: function() {   

              console.log('error_empty_request');
              setTimeout(function(){ grabb() }, 150);

       			 },
			success: function(e){
          
                 addresult= e; 

				//next request
				if(addresult=="ok" || addresult=="no") {

	                displayResults();
	                k++;
					
					 if(addresult=="no") { nextIds[nI] = id; nI++; }

	                if(k<(ids.length) & e.length>0){

						setTimeout(function(){ grabb() }, 150);

	                }else{

	                	endGrabb = 1;
	                	endGrabbNum ++; 
                            
                        //change account
	                	if(a<accMax){
	                		a++;
	                		k=0;
	                		upd=0;
	                		noupd=0;
							createNextIds();
	                		grabb(); 
							
							console.log("y")
	                	}else{

	                		endGrabbAllAccounts = 1;
	                		endGrabbTaskNum ++; 
console.log("n")
	                		if(endGrabbTaskNum==1){update();} 
	                	}

	            	}


				}


				//if !login & !pass (401) 

				if(addresult=="401"){

			         //change account
	                	if(a<accMax){
	                		a++;
	                		k=0;
	                		upd=0;
	                		noupd=0;
							createNextIds();
	                		grabb(); 
	                	}else{

	                		endGrabbAllAccounts = 1;
	                		endGrabbTaskNum ++; 
	                		if(endGrabbTaskNum==1){update();} 
	                	}

				}

			 
			}

		});

	}	

}



$(document).ready(function(){


setTimeout( function(){  $('#acStart').trigger('click');   }, 2000);

$('#acStart').click(function(){

    //get rows
	ids      =[];
	k         =0;
	upd       =0; 
	noupd     =0;
	source = 0;
	a=0; 

     getList(0);
   
	 if(ids.length>0){
	        
		 grabb();

	 }else{

	 	  td = new Date(); 

		$('#r2').html('Not found Numeros with date = '+td.getUTCDate()+'-'+(td.getMonth()+1)+'-'+td.getFullYear()
		+'  or date = NULL   in table numero.');

	}     

});






// $('#acStart2').click(function(){
// 	endUpdateTaskNum = 0;
// 	update(); 
// })

// $('#acStart3').click(function(){
// 	endUpdateTaskNum = 1;
// 	update(); 
// })



});
	
</script>