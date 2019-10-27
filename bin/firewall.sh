#!/usr/bin/env bash
#>                           +---------------+
#>                           |  firewall.sh  |   
#>                           +---------------+
#-
#- SYNOPSIS
#-
#-    firewall.sh [-h] [-i] [-f [file_path]]
#-
#- OPTIONS
#-
#-    -f ?, --file=?       Specify the path of iptables_queue.log
#-    -t ?, --type=?       Specify firewall type. Options:
#-                         firewalld, ufw, iptables, ip6tables
#-    -h, --help           Print this help.
#-    -i, --info           Print script information.
#-
#- EXAMPLES
#-
#-    $ ./firewall.sh -f /tmp/iptables_queue.log -t=ip6tables
#-    $ ./firewall.sh --file=/tmp/iptables_queue.log --type=ip6tables
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
# Part 1. Option (DO NOT MODIFY)
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
            # Specify the path of iptables_queue.log.
            "-f") 
                iptables_file="${2}"
                shift 2
            ;;
            "--file="*) 
                iptables_file="${1#*=}"; 
                shift 1
            ;;
            # Specify Firewall type.
            "-t") 
                firewall_type="${2}"
                shift 2
            ;;
            "--type="*) 
                firewall_type="${1#*=}"; 
                shift 1
            ;;
            # Help
            "-h"|"--help")
                show_script_help
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
# Part 2. Main part.
#==============================================================================

if [ "${firewall_type}" == "firewalld" ]; then
    is_firewalld=$(firewall-cmd --state | grep "running")

    if [ "${is_firewalld}" == "running" ]; then
        while IFS= read -r line; do
            firewall-cmd --zone=drop --add-source=${line}
        done < "${iptables_file}"
    fi
fi

if [ "${firewall_type}" == "ufw" ]; then
    is_utw=$(ufw status | grep "Status: active")

    if [ "${is_utw}" == "active" ]; then
        while IFS= read -r line; do
           ufw deny from ${line} to any
        done < "${iptables_file}"
    fi
fi

if [ "${firewall_type}" == "ip6tables" ]; then
    is_ip6tables=$(ip6tables -L | grep "Chain INPUT")
    if [ "${is_ip6tables}" == "Chain INPUT" ]; then
        while IFS= read -r line; do
            ip6tables -A INPUT -s ${line} -j DROP
        done < "${iptables_file}"
    fi
fi

if [ "${firewall_type}" == "iptables" ]; then
    is_iptables=$(iptables -L | grep "Chain INPUT")
    ip6_regex='^([0-9a-fA-F]{0,4}:){1,7}[0-9a-fA-F]{0,4}$'

    if [ "${is_iptables}" == "Chain INPUT" ]; then
        while IFS= read -r line; do

            if [[ "${line}" =~ "${ip6_regex}" ]]; then
                echo "iptables does not support ipv6 address."
            else
                iptables -A INPUT -s ${line} -j DROP
            fi

        done < "${iptables_file}"
    fi
fi

#==============================================================================
# Part 3. Done. Clean the iptables_queue.log
#==============================================================================

truncate -s 0 ${iptables_file}