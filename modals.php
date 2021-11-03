<div class="modal fade" id="modal-sign-in" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Вход</h5>
                <button type="button" id="modal-sign-in-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="margin-left: auto; margin-right: auto;">
                <table>
                    <tr>
                        <td style="padding-right: 10px;">Пользователь:</td>
                        <td id="modal-sign-in-user-name"></td>
                    </tr>
                    <tr>
                        <td>Пароль:</td>
                        <td>
                            <input type="password" id="modal-sign-in-password" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-sign-in-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;">Войти</button>
                    <button type="button" id="modal-sign-in-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-pass-change" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Изменение пароля</h5>
                <button type="button" id="modal-sign-in-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="margin-left: auto; margin-right: auto;">
                <table>
                    <tr>
                        <td style="padding-right: 10px;">Новый пароль:</td>
                        <td>
                            <input type="password" id="modal-pass-change-pwd1" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-right: 10px;">Подтверждение:</td>
                        <td>
                            <input type="password" id="modal-pass-change-pwd2" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-pass-change-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;">OK</button>
                    <button type="button" id="modal-pass-change-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-info" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header" id="modal-info-header" style="background-color: #4046ff;">
            <h5 class="modal-title" id="modal-info-title" style="color: #FFFFFF;"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body\"><h5 id="modal-info-body-message" style="margin: 10px; text-align: center;"></h5></div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-primary" id="modal-info-btn-ok" data-bs-dismiss="modal" style="width: 150px;">OK</button>
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-are-you-sure" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id=""></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align: center;"><h4 id="modal-are-you-sure-text"></h4></div>
            <div class="modal-footer">
                <div style="margin: auto;">
                    <button type="button" id="modal-are-you-sure-btn-ok" class="btn btn-primary" data-bs-dismiss="modal" style="width: 100px;"></button>
                    <button type="button" id="modal-are-you-sure-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-silo-config-page-change" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header" style="background-color: #520007;">
            <h5 class="modal-title" id="" style="color: white;">Внимание</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body"><h5 id="modal-silo-config-page-change-message"></h5></div>
        <div class="modal-footer">
            <div style="margin: auto;">
                <button type="button" class="btn btn-danger" id="modal-silo-config-page-change-btn-ok" data-bs-dismiss="modal">Все равно перейти</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="modal-ts-connection-settings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <form action="visu_silo_config.php" id="form_ts_settings" method="post" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Настройки подключения к ПО Термосервер</h5>
                <button type="button" id="modal-ts-connection-settings-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                <div class="modal-body col" style="margin-left: auto; margin-right: auto; text-align:center;">
                    <div class="col-12" style="margin-left: auto; margin-right: auto; text-align:center;">
                        <h6 class="modal-title" id="">Введите IP-адрес и порт термосервера.
                                                    Если программа установлена на этом же компьютере, в качестве IP-адреса введите "127.0.0.1".
                                                    Номер порта был выбран при установке.</h5>
                        <br>
                    </div>

                    <div class="col-12">
                        <table style="margin-left: auto; margin-right: auto; text-align:center;">
                            <tr>
                                <td style="padding-right: 10px;">IP:</td>
                                <td>
                                    <input type="text" id="modal-ts-connection-settings-ip" name="POST_ts_connection_settings_ip" value="" oninput="vSConf_checkIP()" class="form-control mx-auto" style="width: 200px;" value=""></input>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-right: 10px;">Порт:</td>
                                <td>
                                    <input type="number" id="modal-ts-connection-settings-port" name="POST_ts_connection_settings_port" value="" onchange="" class="form-control mx-auto" style="width: 200px;" value=""></input>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <br>
                    <h6 class="modal-title" id="">В случае, если была обновлена конфигурация проекта, загрузите новые файлы TermoServer.ini и TermoClient.ini.</h5>
                    <br>

                    <div class="row" style="margin-left: auto; margin-right: auto; width: 100%;">

                        <div class="col g-1" style="display: block; margin: auto;">
                            <label  class="btn btn-light w-100">
                                <i class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                    </svg>
                                </i>TermoServer.ini<input type="file" style="display: none;"  name="POST_termoServerIniFile">
                            </label>
                        </div>

                        <div class="col g-1" style="display: block; margin: auto;">
                            <label  class="btn btn-light w-100">
                                <i class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                    </svg>
                                </i>TermoClient.ini<input type="file" style="display: none;"  name="POST_termoClientIniFile">
                            </label>
                        </div>

                    </div>

                </div>
            
            <div class="modal-footer">
                <div style="margin: auto;">
                    <input id="modal-ts-connection-settings-btn-ok"  type="submit" value="Сохранить" class="btn btn-primary" style="width: 100px;"></input>
                    <button type="button" id="modal-ts-connection-settings-btn-cancel" class="btn btn-secondary" data-bs-dismiss="modal" style="width: 100px;">Отмена</button>
                </div>
            </div>
        </div>
        </form>
    </div>
    
</div>

<div class="modal fade" id="modal-db-operations" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #4046ff;">
                <h5 class="modal-title" id="" style="color: #ffffff;">Операции с Базой Данных</h5>
                <button type="button" id="modal-ts-connection-settings-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body col" style="margin-left: auto; margin-right: auto; text-align:center;">
                
                <div class="row p-1 mb-3">
                    <button type="submit" id="sconf-db-create-backup" class="btn btn-light">
                        Резервное копирование БД
                    </button>
                </div>

                <form action="visu_silo_config.php" method="post" enctype="multipart/form-data">
                    Восстановление БД из резервной копии:
                    <div class="row mb-3">
                        <div class="col-6 g-1">
                            <label  class="btn btn-light w-100">
                                <i class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                    </svg>
                                </i>Файл<input type="file" style="display: none;"  name="databaseBackupFile">
                            </label>
                        </div>
                        <div class="col-6 g-1">
                            <button class="btn btn-light w-100" type="submit" id="sconf-db-restore-from-backup" name="POST_sconf_db_restore_from_backup">
                                Восстановить
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row p-1">
                    <button type="submit" id="sconf-db-truncate-measurements" class="btn btn-light" data-bs-dismiss="modal" >
                        Очистить БД
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>
