jQuery(document).ready(function(){
        jQuery('.toggle').on('click', function(event) {
        	var id = $(this).attr("id");
             jQuery('#'+id+"-text").toggle('show');
        });
    });