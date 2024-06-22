<?php
/*
Copyright (c) 2020 HOSTINPL (HOSTING-RUS) https://vk.com/hosting_rus
Developed by Samir Shelenko (https://vk.com/id00v)
*/
?>
<?php include 'application/views/common/menuserver.php';?>
<div class="kt-content kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
   <div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid" style="margin: 15px 0;">
      <div class="row">
         <div class="col-lg-4 col-xl-4">
            <div class="kt-portlet">
               <div class="kt-portlet__body">
                  <div class="kt-widget kt-widget--general-1">
                     <div class="kt-media kt-media--brand kt-media--md kt-media--circle">
                        <img src="/application/public/img/fastdl.png" alt="image">
                     </div>
                     <div class="kt-widget__wrapper">
                        <div class="kt-widget__label">
                           <a href="#" class="kt-widget__title">
                           FastDL
                           </a>
                           <span class="kt-widget__desc">
                           <?php if($server['fastdl_status'] == 0): ?>
							Выключен
							<?php elseif($server['fastdl_status'] == 1): ?>
							Включен
							<?php endif; ?>
                           </span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="kt-portlet sticky kt-sticky">
               <div class="kt-portlet__body kt-portlet__body--fit">
                  <ul class="kt-nav kt-nav--bold kt-nav--md-space kt-nav--v3 kt-margin-t-5 kt-margin-b-5 nav nav-tabs" role="tablist">
                  	<?php if($server['game_code'] == "cs" or $server['game_code'] == 'css' or $server['game_code'] == 'csgo'): ?>
                  	<?php if($server['fastdl_status'] == 0): ?>
                  	<li class="kt-nav__item">
                        <a class="kt-nav__link" onclick="sendAction('on')">
                        <span class="kt-nav__link-icon"><i class="fa fa-tachometer-alt"></i></span>
                        <span class="kt-nav__link-text">Включить</span>
                        </a>
                     </li>
                     <?php elseif($server['fastdl_status'] == 1): ?>
                     	<li class="kt-nav__item">
                        <a class="kt-nav__link" onclick="sendAction('off')">
                        <span class="kt-nav__link-icon"><i class="fa fa-tachometer-alt"></i></span>
                        <span class="kt-nav__link-text">Выключить</span>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php endif?>
                     <li class="kt-nav__item">
                        <a class="kt-nav__link" data-toggle="modal" data-target="#fastdlinfo">
                        <span class="kt-nav__link-icon"><i class="fa fa-exclamation-circle"></i></span>
                        <span class="kt-nav__link-text">Информация</span>
                        </a>
                     </li>
                     <li class="kt-nav__separator"></li>
                     <li class="kt-nav__item">
                        <a class="kt-nav__link" href="#javascript" data-toggle="kt-tooltip" title="" data-placement="right" data-original-title="Процент нагрузки CPU">
                        <span class="kt-nav__link-icon"><i class="flaticon2-graphic-1"></i></span>
                        <span class="kt-nav__link-text">CPU</span>
                        <span class="kt-nav__link-badge">
                        <span class="kt-badge kt-badge--brand kt-badge--inline kt-badge--rounded"><?php echo $server['server_cpu_load'] ?>/<?php echo $server['game_cores'] * 100 ?>%</span>
                        </span>
                        </a>
                     </li>
                     <li class="kt-nav__item">
                        <a class="kt-nav__link" href="#javascript" data-toggle="kt-tooltip" title="" data-placement="right" data-original-title="Процент нагрузки RAM">
                        <span class="kt-nav__link-icon"><i class="flaticon2-graphic-1"></i></span>
                        <span class="kt-nav__link-text">RAM</span>
                        <span class="kt-nav__link-badge">
                        <span class="kt-badge kt-badge--brand kt-badge--inline kt-badge--rounded"><?php echo $server['server_ram_load'] ?>/100%</span>
                        </span>
                        </a>
                     </li>
                     <li class="kt-nav__item">
                        <a class="kt-nav__link" href="#javascript" data-toggle="kt-tooltip" title="" data-placement="right" data-original-title="Занятость директории">
                        <span class="kt-nav__link-icon"><i class="flaticon2-graphic-1"></i></span>
                        <span class="kt-nav__link-text">SSD</span>
                        <span class="kt-nav__link-badge">
                        <span class="kt-badge kt-badge--brand kt-badge--inline kt-badge--rounded"><?php echo $server['server_ssd_load'] ?>/<?php echo $server['game_ssd'] ?>МБ</span>
                        </span>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
         <div class="col-xl-8 col-lg-8">
            <div class="kt-portlet kt-portlet--height-fluid">
             <div class="kt-portlet__body">
             	 <?php if($server['game_code'] == "cs" or $server['game_code'] == 'css' or $server['game_code'] == 'csgo'): ?>
                 <?php if($server['fastdl_status'] == 0): ?>
                 	<div class="alert alert-info" role="alert">
							<div class="alert-icon"><i class="flaticon2-warning"></i></div>
						  	<div class="alert-text"><strong>Fastdl</strong> Отключен. Для работы необходимо включить функцию.</div>
						</div>
			    	<?php elseif($server['fastdl_status'] == 1): ?>
			    	<div class="alert alert-info" role="alert">
							<div class="alert-text">
							  	<h4 class="alert-heading">Обязательно добавьте в файл server.cfg строчки</h4>
							  	<p>
							  		// FastDL<br>
							  		sv_downloadurl "http://<?php echo $server['location_ip'] ?>/gs<?php echo $server['server_id']?>/cstrike"<br>
							  		sv_consistency 1<br>
							  		sv_allowupload 1 <br>
							  	    sv_allowdownload 1</p>
							  	<hr>
							  	<div class="form-group m-form__group">
						<label for="exampleInputEmail1">Ссылка на сервер</label>
						<input type="text" class="form-control m-input m-input--solid" value="http://<?php echo $server['location_ip'] ?>/gs<?php echo $server['server_id']?>/cstrike" disabled>
					</div>
							</div>
						</div>
					<?php endif; ?>
					<?php else: ?>
						<div class="alert alert-danger" role="alert">
							<div class="alert-icon"><i class="flaticon2-warning"></i></div>
						  	<div class="alert-text">Данная игра не поддерживает <strong>Fastdl</strong></div>
						</div>
					<?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!--begin::Modal-->
<div class="modal fade" id="fastdlinfo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Информация</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            	<p>
            	FastDL — это быстрая загрузка файлов (перевод fast - быстрый/скоростной, DL - сокращенной Download - загрузка) которая поддерживается игровым сервером Counter-Strike (и аналогичных игр) для обеспечения более быстрого подключения клиента к серверу.
                </p>
                <p>
                В обычном режиме мы подключаемся к игровому серверу и медленно скачиваем все необходимые файлы/спрайты/карты, которых у нас еще нет. Скорость отдачи игрового сервера при этом низкая, т.к. помимо игровых запросов ему приходится также отрабатывать обращения на скачивание файлов и, тем самым, скорость замедляется.
                </p>
                <p>
                К счастью Valve предусмотрела это и сделали возможность подключения загрузки файлов игрового сервера со стороннего источника — http сервера, то есть фактически с интернет-сайта. Скорость http подключения сама по себе быстрее, а также серверу не приходится обрабатывать дополнительные соединения, отсутствует серверное ограничение игры (т.е. скорость скачивания равна скорости скачивания с интернета, а на сегодняшний день интернет достаточно развит, чтобы отдавать более быстрое соединения через http). Таким образом мы понимаем что FastDL значительно улучшает скорость скачивания дополнительных файлов сервера, скорость подключения клиента, а также снижает нагрузку на сам игровой сервер.
                </p>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->
<script>
	function sendAction(action) {
		$.ajax({ 
			<?php if($server['game_code'] == "cs" or $server['game_code'] == 'css' or $server['game_code'] == 'csgo'): ?>
			url: '/servers/fastdl/action/'+action+'/<?php echo $server['server_id'] ?>',
			<?php endif?>
			dataType: 'text',
			success: function(data) {
				console.log(data);
				data = $.parseJSON(data);
				switch(data.status) {
					case 'error':
						toastr.error(data.error);
						$('#controlBtns button').prop('disabled', false);
						break;
					case 'success':
						toastr.success(data.success);
						setTimeout("reload()", 1500);
						break;
				}
			}
		});
	}
</script>
<?php echo $footer ?>
