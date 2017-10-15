<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>
<xsl:variable name="imgPath"><xsl:value-of select="$modulesPath"/>xslt_page_builder/img/</xsl:variable>
<xsl:variable name="commonImgPath"><xsl:value-of select="$modulesPath"/>common/img/</xsl:variable>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <div class="row">
        <div class="col-lg-12 text-center">
            <br/><br/><br/>
            <form class="form-horizontal" id="addAdvertForm" action="/{$siteRoot}{/document/dictionaryInfo/moduleName}" method="post" enctype="multipart/form-data" accept-charset="utf-8">
                <div class="form-group">
                    <div class="row">
                        <label class="control-label col-xs-5">Логин</label>
                        <div class="col-xs-2">
                            <input type="text" id="login" class="form-control" name="login" value="" style="width: 100%;"/>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="control-label col-xs-5">Пароль</label>
                        <div class="col-xs-2">
                            <input type="password" id="password" class="form-control" name="password" value="" style="width: 100%;"/>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="control-label col-xs-5"></label>
                        <div class="col-xs-2">
                            <input type="submit" class="btn btn-default" value="Войти"/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</xsl:template>

</xsl:stylesheet>