(function($) {
  $(document).ready(function(){
    var _this = {      
      
      /*
      *CONSTRUCT
      */
      _construct:function(){
        
      },
      download:function(){
      	$("#download").click(function(){
      		$(this).remove();
      	});
      },
      select_tab:function(){
      	$("#tab-list-menu li.tab").click(function(){
      		$("#tab-list-menu li.tab").removeClass("active");
      		$(this).addClass("active");
      		var hash = $(this).find("a").attr("href");      		
      		$("ul.tab-blocks li.tab-content").removeClass("active");
      		$(hash).addClass("active");
      	});
      },
      /*
      *INIT Method
      */
      init: function(){        
        _this.download();
        _this.select_tab();
      },
    }

   /*
   *INIT OBJ
   */
    
      _this.init(); 
      
       
   


  });
})(jQuery);