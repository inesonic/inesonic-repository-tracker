===========================
inesonic-repository-tracker
===========================
You can use this WordPress plugin to track and manage lists of software
repositories referenced by your website.  The plugin provides the following
features:

* Support for multiple projects.

* Tracking of package name, repository URL, and a description for every source
  code repository.

* Generation of tables on a per-project basis or across all projects.  You can
  insert a table simply by adding a WordPress shortcode to your page or post.

To use, simply copy this entire directory into your WordPress plugins directory
and then activate the plugin from the WordPress admin panel.

Once activated, can adjust the settings using the "Source Code" menu option
displayed on the left side of the WordPress Admin panel.


Shortcodes
==========
This plugin provides a single shortcode supporting a number of options.

.. code-block::

   [inesonic_source_code_table
       project="<project>"
       package_name="<package name header>"
       repository_url="<repository url header>"
       description="<description header>"]

Inserting the shortcode will insert a table into your page or post listing
packages required for a given project.  If you exclude the "project"
attribute, then packages for all projects will be listed.

You can optionally set the header text using the "package_name",
"repository_url", and "description" attributes for the shortcode.
