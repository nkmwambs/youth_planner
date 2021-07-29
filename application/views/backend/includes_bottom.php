<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.dataTables.min.css"/>	
    
    
    

	<link rel="stylesheet" href="<?php echo base_url();?>assets/js/datatables/responsive/css/datatables.responsive.css">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/js/select2/select2-bootstrap.css">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/js/select2/select2.css">
	<link rel="stylesheet" href="<?php echo base_url();?>assets/js/selectboxit/jquery.selectBoxIt.css">

   	<!-- Bottom Scripts -->
	<script src="<?php echo base_url();?>assets/js/gsap/main-gsap.js"></script>
	<script src="<?php echo base_url();?>assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
	<!-- <script src="<?php echo base_url();?>assets/js/bootstrap.js"></script> -->
	<script src="<?php echo base_url();?>assets/js/joinable.js"></script>
	<script src="<?php echo base_url();?>assets/js/resizeable.js"></script>
	<script src="<?php echo base_url();?>assets/js/neon-api.js"></script>
	<script src="<?php echo base_url();?>assets/js/toastr.js"></script>
    <script src="<?php echo base_url();?>assets/js/jquery.validate.min.js"></script>
	<script src="<?php echo base_url();?>assets/js/fullcalendar/fullcalendar.min.js"></script>
    <script src="<?php echo base_url();?>assets/js/bootstrap-datepicker.js"></script>
    <script src="<?php echo base_url();?>assets/js/fileinput.js"></script>
    
	<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo base_url();?>assets/js/datatables/TableTools.min.js"></script>
	<script src="<?php echo base_url();?>assets/js/dataTables.bootstrap.js"></script>
	<script src="<?php echo base_url();?>assets/js/datatables/jquery.dataTables.columnFilter.js"></script>
	<script src="<?php echo base_url();?>assets/js/datatables/lodash.min.js"></script>
	<script src="<?php echo base_url();?>assets/js/datatables/responsive/js/datatables.responsive.js"></script>
    <script src="<?php echo base_url();?>assets/js/select2/select2.min.js"></script>
    <script src="<?php echo base_url();?>assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
    
    
    <script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
	<script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.flash.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script>
	<script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script>
	<script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
	<script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"></script>
    
   
    
	<script src="<?php echo base_url();?>assets/js/neon-calendar.js"></script>
	<script src="<?php echo base_url();?>assets/js/neon-chat.js"></script>
	<script src="<?php echo base_url();?>assets/js/neon-custom.js"></script>
	<script src="<?php echo base_url();?>assets/js/neon-demo.js"></script>
	

	
	<!--Font Awesome-->
	
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" /> -->
	<!-- <link rel="stylesheet" href="<?php echo base_url();?>assets/css/font-icons/font-awesome/css/font-awesome.min.css"> -->
	<script src="https://use.fontawesome.com/934e5325aa.js"></script>
	
	<!-- Toggle Button -->
	
	<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
	
		<!-- Monkey Modal Dialog  CSS / JS-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.2/css/bootstrap-dialog.min.css">
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.2/js/bootstrap-dialog.min.js"></script>
	
	
	<!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" rel="stylesheet"> -->
	
	<!--<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">-->


		<!--Dropzone-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/dropzone.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/dropzone.css">
	
	<!-- Bootstrap Switch -->
	<script src="<?php echo base_url();?>assets/js/bootstrap-switch.min.js"></script>	
	
	
		<!--CKEditor Scripts-->
	<script src="<?php echo base_url();?>assets/js/ckeditor/ckeditor.js"></script>
	<script src="<?php echo base_url();?>assets/js/ckeditor/adapters/jquery.js"></script>
		
	
<!-- SHOW TOASTR NOTIFIVATION -->
<?php if ($this->session->flashdata('flash_message') != ""):?>

<script type="text/javascript">
	toastr.success('<?php echo $this->session->flashdata("flash_message");?>');
</script>

<?php endif;?>


<!-----  DATA TABLE EXPORT CONFIGURATIONS ---->                      
<script type="text/javascript">

	jQuery(document).ready(function($)
	{
		

		var datatable = $("#table_export").dataTable();
		
		$(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});
		
		$('.modal-dialog').draggable();
		
		$('.modal-content').resizable({
		    //alsoResize: ".modal-dialog",
		    minHeight: 300,
		    minWidth: 300
		});
		
	});
		
</script>