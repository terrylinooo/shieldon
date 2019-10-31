#!/usr/bin/env bash
#>                           +---------------+
#>                           |  firewall.sh  |   
#>                           +---------------+
#-
#- SYNOPSIS
#-
#-    firewall.sh [-h] [-i] [-w [log_directory]]
#-
#- OPTIONS
#-
#-    -w ?, --watch=?      Watch the directory where the firewall logs are stored.
#-    -c, --clear          Clear all input records.
#-    -h, --help           Print this help.
#-    -i, --info           Print script information.
#-
#- EXAMPLES
#-
#-    $ ./firewall.sh -w /tmp/shieldon_iptable
#-    $ ./firewall.sh --watch=/tmp/shieldon_iptable
#+
#+ IMPLEMENTATION:
#+
#+    package    Shieldon
#+    copyright  https://github.com/terrylinooo/shieldon
#+    license    MIT
#+    authors    Terry Lin (terrylinooo)
#+ 
#==============================================================================

#==============================================================================
# Part 1. Config
#==============================================================================

# iptables_log_folder=""
# iptables_watching_file="iptables_queue.log"
# iptables_default_rules_file="iptables_default_rules.log"
# iptables_status_log_file="iptables_status.log"

#==============================================================================
# Part 2. Option (DO NOT MODIFY)
#==============================================================================

# Print script help
show_script_help() {
    echo 
    head -50 ${0} | grep -e "^#[-|>]" | sed -e "s/^#[-|>]*/ /g"
    echo 
}

# Print script info
show_script_information() {
    echo 
    head -50 ${0} | grep -e "^#[+|>]" | sed -e "s/^#[+|>]*/ /g"
    echo 
}

# Receive arguments.
if [ "$#" -gt 0 ]; then
    while [ "$#" -gt 0 ]; do
        case "$1" in
            # Specify the path of iptables logs' directory.
            "-w") 
                iptables_log_folder="${2}"
                shift 2
            ;;
            "--watch="*) 
                iptables_log_folder="${1#*=}"
                shift 1
            ;;
            # Clear INPUT chain rules.
            "-c"|"--clear")
                # Prevent you block yourself out of the server if the default rule is DENY...
                iptables -P INPUT ACCEPT
                # Flush rules.
                iptables -F INPUT
                exit 1
            ;;
            # Info
            "-i"|"--information")
                show_script_information
                exit 1
            ;;
            "-"*)
                echo "Unknown option: ${1}"
                exit 1
            ;;
            *)
                echo "Unknown option: ${1}"
                exit 1
            ;;
        esac
    done
fi

#==============================================================================
# Part 3. Main part.
#==============================================================================

# Assign absolute path.
iptables_watching_file="${iptables_log_folder}/iptables_queue.log"
iptables_default_rules_file="${iptables_log_folder}/iptables_default_rules.log"
iptables_status_log_file="${iptables_log_folder}/iptables_status.log"

while IFS=';' read -r ip action; do
    ip6tables -A INPUT -s "${ip}" -j "${action}"
done < "${iptables_watching_file}"

#==============================================================================
# Part 4. Done. Empty the iptables_queue.log
#==============================================================================

truncate -s 0 "${iptables_watching_file}"

ip6tables -L > "${iptables_status_log_file}"