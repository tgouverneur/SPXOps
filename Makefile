MY_BUILD_NUMBER=$(or $(BUILD_NUMBER),0)
CONFIG_PATH="rmc_app/config/rmc_config.php"
# @TODO: Set VERSION as needed
VERSION=$(shell echo "0.9.4")
DEB_PACKAGE_NAME=spxops
ARCHIVE=$(DEB_PACKAGE_NAME)_$(VERSION).tgz
ARCHITECTURE=all
TEMPLATE_TO_CLEAN_DEB=$(DEB_PACKAGE_NAME)_$(VERSION)*
COMMIT=$(or $(shell git stash create),HEAD)
VERSION_NAME="$(DEB_PACKAGE_NAME)_$(VERSION).$(MY_BUILD_NUMBER)"
INSTALL_DIR="$(CURDIR)/$(DEB_PACKAGE_NAME)-$(VERSION).$(MY_BUILD_NUMBER)"
DEBIAN_DIR="$(CURDIR)/debian"
DEBEMAIL=thomas@espix.net
DEBFULLNAME="Thomas Gouverneur"
DISTRIBUTION ?= "spx-stretch"
ROOTDIR=/opt/spxops

build_deb_package: $(INSTALL_DIR)
	cd $(INSTALL_DIR); \
	export DEBEMAIL=$(DEBEMAIL); \
	export DEBFULLNAME=$(DEBFULLNAME); \
	fakeroot debchange --newversion $(VERSION).$(MY_BUILD_NUMBER) "New release for $(VERSION).$(MY_BUILD_NUMBER)"; \
	fakeroot debchange --release "Release" --distribution "$(DISTRIBUTION)"; \
	debuild -us -uc

$(INSTALL_DIR): build_archive
	mkdir -p "$(INSTALL_DIR)"
	cp -R "$(DEBIAN_DIR)" "$(INSTALL_DIR)/"
	mkdir -p "$(INSTALL_DIR)/${ROOTDIR}"
	cp -R "$(CURDIR)/app/bin" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/app/sbin" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/app/libs" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/app/plugins" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/app/tpl" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/www" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/doc" "$(INSTALL_DIR)/"
	cp -R "$(CURDIR)/sql" "$(INSTALL_DIR)/"

build_archive:
	git archive --format tar.gz --output "$(ARCHIVE)" $(COMMIT)

build: build_deb_package

publish:
	dput -u $(APT_REPO_HOST) $(VERSION_NAME)*.changes

clean:
	rm -R -f "$(INSTALL_DIR)"
	rm -R -f "$(TEMPLATE_TO_CLEAN_DEB)"
	rm -f "$(ARCHIVE)"
