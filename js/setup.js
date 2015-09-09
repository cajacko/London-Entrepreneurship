( function( $ ) {

    $( document ).ready( documentReadyFunction );
    $( window ).resize( windowResizeFunction );

    function documentReadyFunction() {
        onPageLoadOrResize();
        onPageLoad();
    }

    function windowResizeFunction() {
        onPageLoadOrResize();
    }

    function onPageLoad() {
	    $(function () {
			$('[data-toggle="popover"]').popover()
		})
    }
	
    function onPageLoadOrResize () {
    }
    
    /* -----------------------------
	SUPPORT FUNCTIONS
	----------------------------- */

}) ( jQuery );