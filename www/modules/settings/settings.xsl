<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <xsl:choose>
        <xsl:when test="$userType = $USER_TYPE_ADMIN">
            <div class="row">
                <h3 class="page-header">Settings</h3>
            </div>
            <xsl:call-template name="settings"/>
        </xsl:when>
        <xsl:otherwise>
            Access denied!
        </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="Content"/>
</xsl:template>

<xsl:template name="settings">
    <form class="form" action="/{$siteRoot}manage/{$moduleName}?action=saveSettings" method="post" enctype="multipart/form-data" accept-charset="utf-8">
    <div class="row form">
        <div class="panel panel-default">
            <div class="panel-heading">Settings</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12">
                        <xsl:for-each select="/document/settings/*">
                            <xsl:apply-templates mode="settingsFields" select="."/>
                        </xsl:for-each>
                        <div class="form-group">
                            <input type="submit" class="btn" value="Save"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</xsl:template>

<xsl:template name="settingField" mode="settingsFields" match="*">
    <div class="form-group">
        <label><xsl:value-of select="name()"/>:</label>
        <xsl:variable name="value" select="./value"/>
        <xsl:choose>
            <xsl:when test="type='text'">
                <textarea class="form-control" name="settings[{name()}]" id="settings:{name()}"><xsl:value-of select="value"/></textarea>
            </xsl:when>
            <xsl:when test="type='enum'">
                <select class="form-control" name="settings[{name()}]" id="settings:{name()}">
                    <xsl:for-each select="availibleValues/*">
                        <option value="{value}">
                            <xsl:if test="value = $value">
                                <xsl:attribute name="selected">selected</xsl:attribute>
                            </xsl:if>
                            <xsl:value-of select="name"/>
                        </option>
                    </xsl:for-each>
                </select>
            </xsl:when>
            <xsl:otherwise>
                <input type="text" class="form-control" name="settings[{name()}]" id="settings:{name()}" value="{value}"/>
            </xsl:otherwise>
        </xsl:choose>
    </div>
</xsl:template>

</xsl:stylesheet>