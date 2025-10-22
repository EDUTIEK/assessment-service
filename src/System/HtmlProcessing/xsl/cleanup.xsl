<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    
    <!--  Basic rule: copy nothing -->
    <xsl:template match="*|@*">
    </xsl:template>

    <xsl:template match="html|body">
        <xsl:apply-templates select="*" />
    </xsl:template>

    <!-- keep simple line breaks -->
    <xsl:template match="br">
        <xsl:copy></xsl:copy>
    </xsl:template>

    <!-- copy only allowed elements, without attributes -->
    <xsl:template match="h1|h2|h3|h4|h5|h6|p|ul|ol|li|pre|strong|em|u">
        <xsl:choose>
            <xsl:when test="$service_version >= 20240603">
                <!-- copy empty elements from 2024-03-06 on -->
                <xsl:copy><xsl:apply-templates select="node()" /></xsl:copy>
            </xsl:when>
            <xsl:otherwise>
                <xsl:if test="node()">
                    <xsl:copy><xsl:apply-templates select="node()" /></xsl:copy>
                </xsl:if>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- copy only content -->
    <xsl:template match="span">
        <xsl:apply-templates select="node()" />
    </xsl:template>


</xsl:stylesheet>