<?php
defined('BASEPATH') or exit('No direct script access allowed');

include(VIEWPATH . 'backend/includes_top.php');
include(VIEWPATH . 'backend/includes_bottom.php');
?>



<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary " data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="entypo-plus-squared"></i>
                    <?php echo ('Add Goal'); ?>
                </div>
            </div>
            <div class="panel-body">

                <!-- <form id='frm_add_goal' ,class='form-groups-bordered validate' ,autocomplete="off" ,enctype='multipart/form-data'> -->

                <?php echo form_open(base_url()."goal/post_goal", array('id' => 'frm_post_goal', 'class' => 'form-horizontal form-groups-bordered validate', 'enctype' => 'multipart/form-data')); ?>


                    <div  class="form-group">
                        <label for="" class="col-xs-2 control-label"><?php echo ('Goal Name'); ?></label>
                        <div class="col-xs-10"><INPUT type="text" name="goalname" id="goalname" class="form-control" required="required" /></div>
                    </div>

                    <div  class="form-group">
                        <label for="" class="col-xs-2 control-label"><?php echo ('Theme'); ?></label>
                        <div class="col-xs-10">
                            <select class="form-control" id="theme" name="theme" required="required">
                                <option value=""><?php echo 'select'; ?></option>
                                <option value="1"><?= "Discipliship"; ?></option>
                                <option value="2"><?= "Leadership"; ?></option>
                            </select>


                        </div>


                    </div>

                    <div class="form-group">
                        <label for="" class="col-xs-2 control-label"><?php echo ('Start Date'); ?></label>

                            <div class="col-xs-6 input-group">
                                <input type="text" name="startdate" id="startdate" class="form-control datepicker" data-validate="required" data-message-required="<?php echo ('value_required'); ?>" data-format="yyyy-mm-dd" data-start-date="" data-end-date="" readonly="readonly">

                                <div class="input-group-addon">
                                    <a href="#"><i class="entypo-calendar"></i></a>
                                </div>
                            </div>

                    </div>

                   

                    <div class="form-group">
                        <label for="" class="col-xs-2 control-label"><?php echo ('End Date'); ?></label>

                            <div class="col-xs-6 input-group">
                                <input type="text" name="enddate" id="enddate" class="form-control datepicker" data-validate="required" data-message-required="<?php echo ('value_required'); ?>" data-format="yyyy-mm-dd"  readonly="readonly">

                                <div class="input-group-addon">
                                    <a href="#"><i class="entypo-calendar"></i></a>
                                </div>
                            </div>

                    </div>

                    <div class="col-xs-4">
                        <button id="btn_goal_add" type="submit" class="btn btn-primary btn-icon"><i class="entypo-plus"></i><?php echo ('add'); ?></button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    startDate: '-3d'

                });

            });

    // $('#btn_goal_add').on('click',function(){
    //     var goalname=$('#goalname').val();
    //     var theme=$('#theme').val();
    //     var st_date=$('#startdate').val();
    //     var end_date=$('#enddate').val();

    //     url="<?=base_url()?>goal/post_goal";

    //     var data={
    //         'goal_name':goalname,
    //         'theme_id':theme,
    //         'goal_start_date':st_date,
    //         'goal_end_date':end_date

    //     }

    //     alert(data);
    //     $.post(url,data,function(respose){

    //         alert(response)

    //     });
    // })

          
</script>