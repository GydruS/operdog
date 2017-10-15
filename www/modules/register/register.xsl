<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <xsl:call-template name="registerForm"/>
</xsl:template>

<xsl:template name="registerForm">
    <h1>Регистрация</h1><br/>
    <form id="registerForm" class="form-horizontal" action="/{$siteRoot}{$moduleName}/" method="post" enctype="multipart/form-data" accept-charset="utf-8">
        <xsl:choose>
            <xsl:when test="/document/step = 1">
                <xsl:choose>
                    <xsl:when test="count(/document/context/postedData/*[1]) > 0">
                        <xsl:apply-templates select="/document/context/postedData" mode="fieldsOfStep01"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:apply-templates select="/document" mode="fieldsOfStep01"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="/document/step = 2">
                <input type="hidden" name="step" value="3"/>
                <div class="row">
                    <div class="col-xs-12">
                        <p class="text-info text-center">
                            Вы были успешно зарегистрированны!<br/>
                            Теперь Вы можете войти, использую свои регистрационные данные.<br/>
                            <br/>
                            <input type="submit" class="btn btn-primary" value="Перейти на главную страницу"/>
                        </p>
                    </div>
                </div>
            </xsl:when>
        </xsl:choose>

        <xsl:if test="/document/step &lt; 2">
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-4"></div>
                    <div class="col-xs-6">
                        <input type="submit" class="btn btn-primary" value="Зарегистрироваться"/>
                    </div>
                </div>
            </div>
        </xsl:if>
    </form>
</xsl:template>

<xsl:template match="*" mode="fieldsOfStep01">
    <input type="hidden" name="step" value="2"/>
    <input type="hidden" id="detectedTimezone" name="detectedTimezone" value=""/>
    
    <div class="form-group">
        <div class="row">
            <label for="login" class="control-label col-xs-4">Логин</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" name="user[login]" value="{user/login}" id="login"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label class="control-label col-xs-4">E-mail</label>
            <div class="col-xs-6">
                <input type="email" class="form-control" name="user[email]" value="{user/email}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label for="login" class="control-label col-xs-4">Фамилия</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" name="user[lastname]" value="{user/lastname}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label for="login" class="control-label col-xs-4">Имя</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" name="user[name]" value="{user/name}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label for="login" class="control-label col-xs-4">Отчество</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" name="user[middlename]" value="{user/middlename}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label for="login" class="control-label col-xs-4">Пол</label>
            <div class="col-xs-6">
                <input type="radio" name="user[gender]" value="Male" id="Male" class="">
                    <xsl:if test="(user/gender = 'Male') or (count(/document/user/gender)=0)">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Male" style="font-weight: normal;" class="pointer">&#160;Мужской</label>
                &#160;&#160;&#160;
                <input type="radio" name="user[gender]" value="Female" id="Female" class="">
                    <xsl:if test="user/gender = 'Female'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
                <label for="Female" style="font-weight: normal;" class="pointer">&#160;Женский</label>
            </div>
        </div>
    </div>
    
    <div class="form-group" id="js_visible_pwd" style="display: none;">
        <div class="row">
            <label class="control-label col-xs-4">Пароль</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" id="js_fld_visible_password" name="visible_password" value="{/document/user/password}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group" id="js_pwd">
        <div class="row">
            <label class="control-label col-xs-4">Пароль</label>
            <div class="col-xs-6">
                <div class="input-group">
                    <input type="password" class="form-control" id="js_fld_password1" name="user[password]" value="{/document/user/password}"/>
                    <span class="input-group-btn">
                        <button type="button" id="js_btn_generate" class="btn btn-default">Сгенерировать</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-group" id="js_pwd2">
        <div class="row">
            <label class="control-label col-xs-4">Повторите пароль</label>
            <div class="col-xs-6">
                <input type="password" class="form-control" id="js_fld_password2" name="user[password2]" value="{/document/user/password2}"/>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <div class="col-xs-4"></div>
            <div class="col-xs-6">
                <xsl:variable name="height"><xsl:choose><xsl:when test="$MOBILE = 1">100</xsl:when><xsl:otherwise>50</xsl:otherwise></xsl:choose></xsl:variable>
                <img id="kaptcha" height="{$height}" src="{$modulesPath}../3rdParty/kcaptcha/index.php?{/document/sessionName}={/document/sessionId}"/>
                <br/>
                <span class="small"><a id="reloadKaptcha" class="pointer" style="text-decoration:none;">Показать другой код проверки</a></span>
            </div>
        </div>
    </div>
    
    <div class="form-group" id="js_pwd2">
        <div class="row">
            <label class="control-label col-xs-4">Код проверки</label>
            <div class="col-xs-6">
                <input type="text" class="form-control" name="captcha" value=""/>
            </div>
        </div>
    </div>
</xsl:template>

</xsl:stylesheet>