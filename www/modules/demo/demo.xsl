<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template match="/document">
	<xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <h1>
        Welcome to GeThree!
    </h1>
    <p>
        <xsl:value-of select="dynamicText"/>
    </p>
</xsl:template>

</xsl:stylesheet>