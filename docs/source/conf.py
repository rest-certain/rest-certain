# Configuration file for the Sphinx documentation builder.

import os
import sphinx_rtd_theme
import sys
import datetime

from pygments.lexers.web import PhpLexer
from sphinx.highlighting import lexers
from subprocess import Popen, PIPE

def get_version():
    if os.environ.get('READTHEDOCS') == 'True':
        return os.environ.get('READTHEDOCS_VERSION')

    pipe = Popen('git branch | grep \\*', stdout=PIPE, shell=True, universal_newlines=True)
    version = pipe.stdout.read()

    if version:
        return version[2:]
    else:
        return 'unknown'

# -- Project information
project = 'rest-certain/rest-certain'
copyright = '%Y, REST Certain Contributors'
author = 'REST Certain Contributors'

version = get_version().strip()
release = version

today_fmt = '%Y-%m-%d'

# -- General configuration
numfig = True
highlight_language = 'php'
highlight_options = {
    'php': {'startinline': True},
}
primary_domain = 'php'
maximum_signature_line_length = 100
templates_path = ['_templates']
pygments_style = 'sphinx'

current_year = datetime.date.today().strftime('%Y')
rst_prolog = """
.. |current_year| replace:: {0}
""".format(current_year)

extensions = [
    'sphinx.ext.autosummary',
    'sphinx.ext.duration',
    'sphinx.ext.intersphinx',
    'sphinx.ext.todo',
    'sphinxcontrib.phpdomain',
]

intersphinx_mapping = {
    'phpunit': ('https://docs.phpunit.de/en/12.1/', None),
    'hamcrest': ('https://hamcrest-phpunit.readthedocs.io/en/latest/', None),
}
intersphinx_disabled_reftypes = ['std:doc']
todo_include_todos = True

# -- Options for HTML output
html_theme = 'sphinx_rtd_theme'
html_theme_options = {
    'version_selector': True,
    'includehidden': False,
    'collapse_navigation': False,
}
html_static_path = ['_static']
html_title = 'REST Certain, %s' % version
html_short_title = 'REST Certain'
html_context = {
    'display_github': True,
    'github_user': 'rest-certain',
    'github_repo': 'rest-certain',
    'github_version': version,
    'conf_py_path': '/docs/source/',
}

# -- Options for EPUB output
epub_show_urls = 'footnote'
